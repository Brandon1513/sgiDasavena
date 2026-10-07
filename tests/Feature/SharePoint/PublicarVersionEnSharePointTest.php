<?php

use App\Jobs\MoverVersionASharePointObsoletos;
use App\Jobs\PublicarVersionEnSharePoint;
use App\Mail\PublicacionSharePointFallidaMailable;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

const RAIZ = 'Sistema de Gestión de Inocuidad/SGI';

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    // Evita que GraphAccessTokenProvider intente pedir un token real: lo
    // deja precargado en caché (usa el driver "array" de phpunit.xml).
    Cache::put('graph_api_access_token', 'token-de-prueba', 3600);

    Storage::fake('public');

    $this->adminSgi = User::factory()->create();
    $this->adminSgi->assignRole('administrador_sgi');
});

function fakeGraphGenerico(): void
{
    Http::fake(function ($request) {
        $url = $request->url();
        $method = $request->method();

        if (str_contains($url, '/children') && preg_match('/\$select=/', $url) && $method === 'GET') {
            return Http::response(['value' => []], 200);
        }

        if (str_contains($url, '/children') && $method === 'POST') {
            return Http::response(['id' => 'folder-' . Str::random(8), 'name' => 'carpeta'], 201);
        }

        if (str_contains($url, '/createUploadSession')) {
            return Http::response(['uploadUrl' => 'https://upload.example.com/session-abc'], 200);
        }

        if (str_contains($url, 'upload.example.com')) {
            return Http::response(['id' => 'item-grande', 'webUrl' => 'https://sharepoint.example/big-file'], 200);
        }

        if (str_contains($url, ':/content') && $method === 'PUT') {
            return Http::response(['id' => 'item-nuevo', 'webUrl' => 'https://sharepoint.example/archivo.pdf'], 200);
        }

        if ($method === 'PATCH') {
            return Http::response(['id' => 'item-movido'], 200);
        }

        return Http::response(['error' => 'unhandled: ' . $method . ' ' . $url], 404);
    });
}

/**
 * Simula el árbol real observado en SharePoint: raíz con carpetas por tipo
 * (vigentes) + "Sistema de Gestión Obsoleto" con carpetas por Área, cada una
 * con subcarpetas "{Tipo} obsoletos" (algunas en singular, como la realidad).
 */
function fakeArbolRealObsoletos(): void
{
    $ids = [
        'raiz-inocuidad' => 'id-raiz-inocuidad',
        'sgi' => 'id-sgi',
        'obsoleto-root' => 'id-obsoleto-root',
        'area-calidad' => 'id-area-calidad',
        'area-produccion' => 'id-area-produccion',
        'formatos-obsoletos-calidad' => 'id-formatos-obsoletos-calidad',
        'manual-obsoletos-produccion' => 'id-manual-obsoletos-produccion',
    ];

    Http::fake(function ($request) use ($ids) {
        $url = $request->url();
        $method = $request->method();

        // obsoleto_root_folder = "Sistema de Gestión de Inocuidad/SGI/Sistema
        // de Gestión Obsoleto": tres saltos desde la raíz del drive.
        if (preg_match('#/items/root/children#', $url) && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['raiz-inocuidad'], 'name' => 'Sistema de Gestión de Inocuidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, "/items/{$ids['raiz-inocuidad']}/children") && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['sgi'], 'name' => 'SGI', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, "/items/{$ids['sgi']}/children") && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['obsoleto-root'], 'name' => 'Sistema de Gestión Obsoleto', 'folder' => ['childCount' => 2]],
            ]], 200);
        }

        if (str_contains($url, "/items/{$ids['obsoleto-root']}/children") && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['area-calidad'], 'name' => 'Calidad', 'folder' => ['childCount' => 1]],
                ['id' => $ids['area-produccion'], 'name' => 'Producción', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, "/items/{$ids['area-calidad']}/children") && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['formatos-obsoletos-calidad'], 'name' => 'Formatos obsoletos', 'folder' => ['childCount' => 0]],
            ]], 200);
        }

        if (str_contains($url, "/items/{$ids['area-produccion']}/children") && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => $ids['manual-obsoletos-produccion'], 'name' => 'Manual obsoletos', 'folder' => ['childCount' => 0]],
            ]], 200);
        }

        if (str_contains($url, '/children') && $method === 'POST') {
            return Http::response(['id' => 'folder-nueva-' . Str::random(6), 'name' => 'nueva'], 201);
        }

        if ($method === 'PATCH') {
            return Http::response(['id' => 'item-movido'], 200);
        }

        if (str_contains($url, ':/content') && $method === 'PUT') {
            return Http::response(['id' => 'item-nuevo', 'webUrl' => 'https://sharepoint.example/archivo.pdf'], 200);
        }

        // Cualquier otra carpeta no modelada aquí (p. ej. la carpeta de
        // vigentes + Código, que en estos tests no forma parte del árbol de
        // Obsoletos que se está probando) se resuelve como "no existe
        // todavía" para que ensureFolderPath la cree sin tronar.
        if (str_contains($url, '/children') && $method === 'GET') {
            return Http::response(['value' => []], 200);
        }

        return Http::response(['error' => 'unhandled: ' . $method . ' ' . $url], 404);
    });
}

/**
 * Simula "Sistema de Gestión de Inocuidad/Calidad/Procedimientos" para
 * probar el cálculo de la ubicación sugerida de vigentes (Área + Tipo).
 */
function fakeArbolVigentePorArea(): void
{
    Http::fake(function ($request) {
        $url = $request->url();
        $method = $request->method();

        if (preg_match('#/items/root/children#', $url) && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-raiz-inocuidad', 'name' => 'Sistema de Gestión de Inocuidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-raiz-inocuidad/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-area-calidad', 'name' => 'Calidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-area-calidad/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-procedimientos-calidad', 'name' => 'Procedimientos', 'folder' => ['childCount' => 0]],
            ]], 200);
        }

        return Http::response(['error' => 'unhandled: ' . $method . ' ' . $url], 404);
    });
}

test('finalize_form muestra la ubicación sugerida cuando el área y el tipo son identificables', function () {
    fakeArbolVigentePorArea();

    $solicitante = User::factory()->create(['area' => 'Calidad']);
    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Procedimiento de prueba',
        'tipo_documento' => 'Anexo Procedimiento',
    ]);

    $this->actingAs($this->adminSgi)
        ->get(route('solicitudes.finalize_form', $solicitud))
        ->assertOk()
        ->assertSee('Sistema de Gestión de Inocuidad/Calidad/Procedimientos', false);
});

test('finalize_form no sugiere nada cuando el área del solicitante no tiene match confiable', function () {
    fakeArbolVigentePorArea();

    $solicitante = User::factory()->create(['area' => 'Gerencia de Talento y Cultura']);
    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Documento ambiguo',
        'tipo_documento' => 'Procedimiento',
    ]);

    $this->actingAs($this->adminSgi)
        ->get(route('solicitudes.finalize_form', $solicitud))
        ->assertOk()
        ->assertSee('No se pudo sugerir una ubicación automática');
});

test('finalizar una solicitud con archivo oficial despacha el job y deja la versión en pendiente', function () {
    Queue::fake();

    $solicitante = User::factory()->create();
    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Procedimiento de prueba',
        'tipo_documento' => 'Procedimiento',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/referencia-externa.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'DOC-SP-001',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
            'archivo_oficial' => UploadedFile::fake()->create('oficial.pdf', 500, 'application/pdf'),
            'sp_carpeta_vigente_path' => RAIZ . '/Procedimientos',
        ])
        ->assertRedirect(route('solicitudes.index'));

    $doc = Documento::where('codigo', 'DOC-SP-001')->firstOrFail();
    $version = $doc->fresh()->versionVigente;

    expect($version->sp_estado)->toBe('pendiente')
        ->and($version->sp_folder_path)->toBe(RAIZ . '/Procedimientos')
        ->and($version->archivo_storage)->not->toBeNull();

    Queue::assertPushed(PublicarVersionEnSharePoint::class);
});

test('finalizar sin elegir ubicación cuando no hay sugerencia automática falla con un error claro', function () {
    Queue::fake();

    $solicitante = User::factory()->create();
    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Documento sin tipo claro',
        'tipo_documento' => 'Anexo',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/referencia-externa.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'DOC-SP-010',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
            'archivo_oficial' => UploadedFile::fake()->create('oficial.pdf', 500, 'application/pdf'),
            // sin sp_carpeta_vigente_path: no hubo sugerencia y no se eligió a mano
        ])
        ->assertSessionHasErrors('sp_carpeta_vigente_path');

    expect(Documento::where('codigo', 'DOC-SP-010')->exists())->toBeFalse();
    Queue::assertNotPushed(PublicarVersionEnSharePoint::class);
});

test('el job sube el archivo a la carpeta ya decidida y guarda sp_item_id/sp_web_url', function () {
    fakeGraphGenerico();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-002',
        'nombre' => 'Documento de prueba',
        'area' => 'Calidad',
        'tipo_documento' => 'Procedimiento',
        'estatus' => 'vigente',
    ]);

    $nueva = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-NEW',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_folder_path' => RAIZ . '/Procedimientos',
    ]);

    $doc->update(['version_vigente_id' => $nueva->id]);

    $archivo = UploadedFile::fake()->create('oficial.pdf', 500, 'application/pdf');
    $path = $archivo->store('documentos-oficiales', 'public');

    (new PublicarVersionEnSharePoint($doc->id, $nueva->id, $path))
        ->handle(app(\App\Actions\SharePoint\PublishDocumentoVersion::class));

    $nueva->refresh();

    expect($nueva->sp_estado)->toBe('publicado')
        ->and($nueva->sp_item_id)->toBe('item-nuevo')
        ->and($nueva->sp_web_url)->toBe('https://sharepoint.example/archivo.pdf');
});

test('al publicar, la vigente anterior se mueve a la carpeta de Obsoletos que ya existe para su Área y tipo', function () {
    fakeArbolRealObsoletos();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-011',
        'nombre' => 'Documento con versión previa',
        'area' => 'Calidad',
        'tipo_documento' => 'Formato',
        'estatus' => 'vigente',
    ]);

    $anterior = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-OLD',
        'estatus' => 'obsoleto',
        'revision_actual' => '0',
        'sp_item_id' => 'item-anterior',
    ]);

    $nueva = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-NEW',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_folder_path' => RAIZ . '/Formatos',
    ]);

    $doc->update(['version_vigente_id' => $nueva->id]);

    $archivo = UploadedFile::fake()->create('oficial.pdf', 500, 'application/pdf');
    $path = $archivo->store('documentos-oficiales', 'public');

    (new PublicarVersionEnSharePoint($doc->id, $nueva->id, $path, $anterior->id))
        ->handle(app(\App\Actions\SharePoint\PublishDocumentoVersion::class));

    $anterior->refresh();

    // "Calidad" -> Área real "Calidad"; "Formato" -> ya existe "Formatos obsoletos" ahí: se reutiliza, no se crea.
    expect($anterior->sp_folder_path)->toBe(RAIZ . '/Sistema de Gestión Obsoleto/Calidad/Formatos obsoletos');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH');
    // No se crea ninguna carpeta dentro del Área de Obsoletos: se reutilizó
    // la que ya existía ("Formatos obsoletos").
    Http::assertNotSent(fn ($request) => $request->method() === 'POST' && str_contains($request->url(), '/items/id-area-calidad/children'));
});

test('respeta una carpeta de obsoletos ya existente aunque esté en singular, sin crear una nueva', function () {
    fakeArbolRealObsoletos();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-012',
        'nombre' => 'Documento de Producción',
        'area' => 'Producción',
        'tipo_documento' => 'Manual',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'obsoleto',
        'revision_actual' => '0',
        'sp_item_id' => 'item-manual-produccion',
    ]);

    app(\App\Actions\SharePoint\PublishDocumentoVersion::class)->moverAObsoletos($doc, $version);

    $version->refresh();

    expect($version->sp_folder_path)->toBe(RAIZ . '/Sistema de Gestión Obsoleto/Producción/Manual obsoletos');
});

test('crea la carpeta de obsoletos solo cuando esa combinación Área+Tipo nunca existió', function () {
    fakeArbolRealObsoletos();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-013',
        'nombre' => 'Documento de Calidad sin manuales obsoletos aún',
        'area' => 'Calidad',
        'tipo_documento' => 'Manual',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'obsoleto',
        'revision_actual' => '0',
        'sp_item_id' => 'item-manual-calidad',
    ]);

    app(\App\Actions\SharePoint\PublishDocumentoVersion::class)->moverAObsoletos($doc, $version);

    $version->refresh();

    // "Calidad" solo tiene "Formatos obsoletos" en el árbol simulado: para
    // Manual nunca existió, así que se crea siguiendo el patrón plural.
    expect($version->sp_folder_path)->toBe(RAIZ . '/Sistema de Gestión Obsoleto/Calidad/Manuales obsoletos');

    Http::assertSent(fn ($request) => $request->method() === 'POST' && str_contains($request->url(), '/children'));
});

test('si el Área no tiene match confiable, no mueve nada y lo deja registrado en el log', function () {
    fakeArbolRealObsoletos();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-014',
        'nombre' => 'Documento de un área sin equivalente real',
        'area' => 'Gerencia de Talento y Cultura',
        'tipo_documento' => 'Formato',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'obsoleto',
        'revision_actual' => '0',
        'sp_item_id' => 'item-area-rara',
        'sp_folder_path' => 'ubicación-original',
    ]);

    app(\App\Actions\SharePoint\PublishDocumentoVersion::class)->moverAObsoletos($doc, $version);

    $version->refresh();

    // No cambió: no se movió nada.
    expect($version->sp_folder_path)->toBe('ubicación-original');

    Http::assertNotSent(fn ($request) => $request->method() === 'PATCH');
});

test('un archivo de más de 4 MB usa upload session en vez de subida simple', function () {
    fakeGraphGenerico();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-003',
        'nombre' => 'Documento con archivo grande',
        'area' => 'Calidad',
        'tipo_documento' => 'Formato',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_folder_path' => RAIZ . '/Formatos',
    ]);

    // UploadedFile::fake()->create() no escribe bytes reales en disco (solo
    // reporta un tamaño falso), así que para probar el límite de 4 MB real
    // se escribe el contenido directamente en el disco falso.
    $path = 'documentos-oficiales/grande.pdf';
    Storage::disk('public')->put($path, str_repeat('a', 5 * 1024 * 1024));

    (new PublicarVersionEnSharePoint($doc->id, $version->id, $path))
        ->handle(app(\App\Actions\SharePoint\PublishDocumentoVersion::class));

    $version->refresh();

    expect($version->sp_item_id)->toBe('item-grande');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/createUploadSession'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'upload.example.com'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), ':/content') && $request->method() === 'PUT');
});

test('un error 5xx persistente deja sp_estado=error, notifica al administrador_sgi y la versión sigue vigente', function () {
    Mail::fake();

    Http::fake(function ($request) {
        if (str_contains($request->url(), '/children') && $request->method() === 'GET') {
            return Http::response(['value' => []], 200);
        }

        // Todo lo demás (crear carpeta, subir archivo) falla persistentemente.
        return Http::response(['error' => 'falla simulada'], 503);
    });

    $doc = Documento::create([
        'codigo' => 'DOC-SP-004',
        'nombre' => 'Documento con falla de publicación',
        'area' => 'Calidad',
        'tipo_documento' => 'Formato',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_folder_path' => RAIZ . '/Formatos',
    ]);

    $doc->update(['version_vigente_id' => $version->id]);

    $archivo = UploadedFile::fake()->create('oficial.pdf', 200, 'application/pdf');
    $path = $archivo->store('documentos-oficiales', 'public');

    $job = new PublicarVersionEnSharePoint($doc->id, $version->id, $path);

    expect(fn () => $job->handle(app(\App\Actions\SharePoint\PublishDocumentoVersion::class)))
        ->toThrow(\RuntimeException::class);

    $job->failed(new \RuntimeException('falla simulada'));

    $version->refresh();

    expect($version->sp_estado)->toBe('error')
        ->and($version->sp_error)->not->toBeNull()
        ->and($version->estatus)->toBe('vigente');

    Mail::assertSent(PublicacionSharePointFallidaMailable::class, function ($mail) {
        return $mail->hasTo($this->adminSgi->email);
    });
});

test('un usuario sin rol administrador_sgi no puede usar "Reintentar publicación"', function () {
    $doc = Documento::create([
        'codigo' => 'DOC-SP-005',
        'nombre' => 'Documento con error de publicación',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_estado' => 'error',
        'sp_error' => 'falla previa',
        'archivo_storage' => 'documentos-oficiales/algo.pdf',
    ]);

    $usuarioComun = User::factory()->create();
    $usuarioComun->assignRole('usuario');

    $this->actingAs($usuarioComun)
        ->post(route('documento_versiones.sharepoint.reintentar', $version))
        ->assertForbidden();
});

test('un administrador_sgi sí puede reintentar la publicación', function () {
    Queue::fake();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-006',
        'nombre' => 'Documento con error de publicación',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_estado' => 'error',
        'sp_error' => 'falla previa',
        'archivo_storage' => 'documentos-oficiales/algo.pdf',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('documento_versiones.sharepoint.reintentar', $version))
        ->assertRedirect();

    expect($version->fresh()->sp_estado)->toBe('pendiente');

    Queue::assertPushed(PublicarVersionEnSharePoint::class);
});

test('sharepoint:publicar-existentes --dry-run no despacha nada', function () {
    Queue::fake();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-007',
        'nombre' => 'Documento sin publicar',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $archivo = UploadedFile::fake()->create('oficial.pdf', 200, 'application/pdf');
    $path = $archivo->store('documentos-oficiales', 'public');

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'archivo_storage' => $path,
    ]);

    $doc->update(['version_vigente_id' => $version->id]);

    $this->artisan('sharepoint:publicar-existentes', ['--dry-run' => true])
        ->assertSuccessful();

    Queue::assertNotPushed(PublicarVersionEnSharePoint::class);
    expect($version->fresh()->sp_estado)->toBeNull();
});

test('dar de baja un documento con versión ya publicada despacha el job de moverla a Obsoletos', function () {
    Queue::fake();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-008',
        'nombre' => 'Documento a dar de baja',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'sp_item_id' => 'item-existente',
    ]);

    $doc->update(['version_vigente_id' => $version->id]);

    $this->actingAs($this->adminSgi)
        ->post(route('documentos.baja', $doc->id))
        ->assertRedirect();

    Queue::assertPushed(MoverVersionASharePointObsoletos::class);
});

test('los accessors de vencimiento del Documento no cambiaron con la integración de SharePoint', function () {
    $doc = Documento::create([
        'codigo' => 'DOC-SP-009',
        'nombre' => 'Documento de control',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
        'fecha_vencimiento_version' => now()->addDays(10)->toDateString(),
        'fecha_vencimiento_revision' => now()->addDays(40)->toDateString(),
    ]);

    $doc->update(['version_vigente_id' => $version->id]);
    $doc->refresh();

    expect($doc->dias_para_vencimiento)->toBe(10)
        ->and($doc->semaforo_vencimiento)->toBe('critico');
});
