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

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    // Evita que GraphAccessTokenProvider intente pedir un token real: lo
    // deja precargado en caché (usa el driver "array" de phpunit.xml).
    Cache::put('graph_api_access_token', 'token-de-prueba', 3600);

    Storage::fake('public');

    $this->adminSgi = User::factory()->create();
    $this->adminSgi->assignRole('administrador_sgi');
});

function fakeGraphParaPublicar(): void
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

test('finalizar una solicitud con archivo oficial despacha el job y deja la versión en pendiente', function () {
    Queue::fake();

    $solicitante = User::factory()->create();
    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Procedimiento de prueba',
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
        ])
        ->assertRedirect(route('solicitudes.index'));

    $doc = Documento::where('codigo', 'DOC-SP-001')->firstOrFail();
    $version = $doc->fresh()->versionVigente;

    expect($version->sp_estado)->toBe('pendiente')
        ->and($version->archivo_storage)->not->toBeNull();

    Queue::assertPushed(PublicarVersionEnSharePoint::class);
});

test('el job sube el archivo, guarda sp_item_id/sp_web_url y mueve la vigente anterior a Obsoletos', function () {
    fakeGraphParaPublicar();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-002',
        'nombre' => 'Documento con versión previa',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $anterior = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-OLD',
        'estatus' => 'obsoleto',
        'revision_actual' => '0',
        'sp_item_id' => 'item-anterior',
        'sp_drive_id' => 'drive-1',
    ]);

    $nueva = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-NEW',
        'estatus' => 'vigente',
        'revision_actual' => '0',
    ]);

    $doc->update(['version_vigente_id' => $nueva->id]);

    $archivo = UploadedFile::fake()->create('oficial.pdf', 500, 'application/pdf');
    $path = $archivo->store('documentos-oficiales', 'public');

    (new PublicarVersionEnSharePoint($doc->id, $nueva->id, $path, $anterior->id))
        ->handle(app(\App\Actions\SharePoint\PublishDocumentoVersion::class));

    $nueva->refresh();

    expect($nueva->sp_estado)->toBe('publicado')
        ->and($nueva->sp_item_id)->toBe('item-nuevo')
        ->and($nueva->sp_web_url)->toBe('https://sharepoint.example/archivo.pdf');

    Http::assertSent(fn ($request) => $request->method() === 'PATCH');
});

test('un archivo de más de 4 MB usa upload session en vez de subida simple', function () {
    fakeGraphParaPublicar();

    $doc = Documento::create([
        'codigo' => 'DOC-SP-003',
        'nombre' => 'Documento con archivo grande',
        'area' => 'Calidad',
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
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
        'estatus' => 'vigente',
    ]);

    $version = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-1',
        'estatus' => 'vigente',
        'revision_actual' => '0',
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
