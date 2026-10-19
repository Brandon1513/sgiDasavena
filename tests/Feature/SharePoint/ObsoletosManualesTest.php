<?php

use App\Actions\SharePoint\PublishDocumentoVersion;
use App\Jobs\MoverArchivosSharePointAObsoletos;
use App\Jobs\MoverVersionASharePointObsoletos;
use App\Jobs\PublicarVersionEnSharePoint;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Archivos previos al sistema (subidos a mano a SharePoint, sin sp_item_id)
 * que el administrador marca en el selector de finalize_form para retirarlos
 * a Obsoletos, y la baja por solicitud.
 */

const OM_RAIZ = 'Sistema de Gestión de Inocuidad';
const OM_OBSOLETOS = 'Sistema de Gestión de Inocuidad/SGI/Sistema de Gestión Obsoleto';

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    Cache::put('graph_api_access_token', 'token-de-prueba', 3600);
    Storage::fake('public');

    config([
        'sharepoint.site_id' => 'site-prueba',
        'sharepoint.drive_id' => 'drive-prueba',
        'sharepoint.root_folder' => OM_RAIZ,
        'sharepoint.obsoleto_root_folder' => OM_OBSOLETOS,
    ]);

    $this->adminSgi = User::factory()->create();
    $this->adminSgi->assignRole('administrador_sgi');
});

/**
 * Árbol: raíz de vigentes → SGI → "Sistema de Gestión Obsoleto" → Calidad →
 * "Formatos obsoletos"; más vigentes Calidad/Formatos con dos archivos, y
 * archivos sueltos para probar el filtro de seguridad.
 */
function fakeArbolObsoletosManuales(): void
{
    $drive = '/drives/drive-prueba/root:';
    $items = [
        'legacy-1' => ['name' => 'F-001 Rev 2.pdf', 'path' => "{$drive}/" . OM_RAIZ . '/Calidad/Formatos', 'file' => true],
        'legacy-2' => ['name' => 'F-001 Rev 1.pdf', 'path' => "{$drive}/" . rawurlencode(OM_RAIZ) . '/Calidad/Formatos', 'file' => true],
        'fuera-1' => ['name' => 'Personal.xlsx', 'path' => "{$drive}/Otra biblioteca/Personal", 'file' => true],
        'ya-obsoleto-1' => ['name' => 'F-000.pdf', 'path' => "{$drive}/" . OM_OBSOLETOS . '/Calidad/Formatos obsoletos', 'file' => true],
        'carpeta-1' => ['name' => 'Formatos', 'path' => "{$drive}/" . OM_RAIZ . '/Calidad', 'file' => false],
    ];

    Http::fake(function ($request) use ($items) {
        $url = urldecode($request->url());
        $method = $request->method();

        $hijos = [
            '/items/root/children' => [['id' => 'id-raiz', 'name' => OM_RAIZ, 'folder' => ['childCount' => 2]]],
            '/items/id-raiz/children' => [
                ['id' => 'id-sgi', 'name' => 'SGI', 'folder' => ['childCount' => 1]],
                ['id' => 'id-calidad', 'name' => 'Calidad', 'folder' => ['childCount' => 1]],
            ],
            '/items/id-sgi/children' => [['id' => 'id-obsoleto-root', 'name' => 'Sistema de Gestión Obsoleto', 'folder' => ['childCount' => 1]]],
            '/items/id-obsoleto-root/children' => [['id' => 'id-obs-calidad', 'name' => 'Calidad', 'folder' => ['childCount' => 1]]],
            '/items/id-obs-calidad/children' => [['id' => 'id-formatos-obsoletos', 'name' => 'Formatos obsoletos', 'folder' => ['childCount' => 0]]],
            '/items/id-calidad/children' => [['id' => 'id-formatos', 'name' => 'Formatos', 'folder' => ['childCount' => 2]]],
            '/items/id-formatos/children' => [
                ['id' => 'legacy-1', 'name' => 'F-001 Rev 2.pdf', 'file' => ['mimeType' => 'application/pdf'], 'webUrl' => 'https://sp/legacy-1', 'size' => 1200, 'lastModifiedDateTime' => '2024-03-11T10:00:00Z'],
                ['id' => 'id-sub', 'name' => 'Anexos', 'folder' => ['childCount' => 0]],
            ],
        ];

        if ($method === 'GET') {
            foreach ($hijos as $ruta => $value) {
                if (str_contains($url, $ruta)) {
                    return Http::response(['value' => $value], 200);
                }
            }

            if (str_contains($url, '/children')) {
                return Http::response(['value' => []], 200);
            }

            foreach ($items as $id => $item) {
                if (preg_match("#/items/{$id}$#", $url)) {
                    return Http::response(array_filter([
                        'id' => $id,
                        'name' => $item['name'],
                        'file' => $item['file'] ? ['mimeType' => 'application/pdf'] : null,
                        'folder' => $item['file'] ? null : ['childCount' => 1],
                        'parentReference' => ['path' => $item['path']],
                    ]), 200);
                }
            }
        }

        if ($method === 'POST' && str_contains($url, '/children')) {
            return Http::response(['id' => 'folder-' . Str::random(6), 'name' => 'nueva'], 201);
        }

        if ($method === 'PATCH') {
            preg_match('#/items/([^/?]+)#', $url, $m);

            return Http::response(['id' => $m[1], 'webUrl' => "https://sp/obsoletos/{$m[1]}"], 200);
        }

        if ($method === 'PUT' && str_contains($url, ':/content')) {
            return Http::response(['id' => 'item-nuevo', 'webUrl' => 'https://sp/nuevo.pdf'], 200);
        }

        return Http::response(['error' => "unhandled: {$method} {$url}"], 404);
    });
}

function documentoConVersionLegacy(): array
{
    $doc = Documento::create([
        'codigo' => 'F-001',
        'nombre' => 'Formato de inspección',
        'area' => 'Calidad',
        'tipo_documento' => 'Formato',
        'estatus' => 'vigente',
    ]);

    // Versión importada antes del sistema: su archivo está en SharePoint pero sin sp_item_id.
    $anterior = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-OLD',
        'estatus' => 'obsoleto',
        'revision_actual' => '1',
    ]);

    $nueva = DocumentoVersion::create([
        'documento_id' => $doc->id,
        'version' => 'VER-NEW',
        'estatus' => 'vigente',
        'revision_actual' => '2',
        'sp_folder_path' => OM_RAIZ . '/Calidad/Formatos',
    ]);

    $doc->update(['version_vigente_id' => $nueva->id]);

    return [$doc, $anterior, $nueva];
}

test('al publicar, los archivos marcados se mueven a Obsoletos ANTES de subir el nuevo y el primero queda ligado a la versión anterior', function () {
    fakeArbolObsoletosManuales();
    [$doc, $anterior, $nueva] = documentoConVersionLegacy();

    $path = UploadedFile::fake()->create('oficial.pdf', 200, 'application/pdf')->store('documentos-oficiales', 'public');

    (new PublicarVersionEnSharePoint($doc->id, $nueva->id, $path, $anterior->id, false, null, ['legacy-1', 'legacy-2']))
        ->handle(app(PublishDocumentoVersion::class));

    $metodos = collect(Http::recorded())->map(fn ($par) => $par[0]->method() . ' ' . urldecode($par[0]->url()))->values();
    $primerPut = $metodos->search(fn ($m) => str_starts_with($m, 'PUT'));
    $patches = $metodos->keys()->filter(fn ($i) => str_starts_with($metodos[$i], 'PATCH'));

    expect($patches)->toHaveCount(2)
        ->and($patches->max())->toBeLessThan($primerPut);

    // Ambos van a la carpeta de Obsoletos ya existente, renombrando si hay choque de nombre.
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains(urldecode($r->url()), '@microsoft.graph.conflictBehavior=rename')
        && $r['parentReference']['id'] === 'id-formatos-obsoletos');

    $anterior->refresh();
    expect($anterior->sp_item_id)->toBe('legacy-1')
        ->and($anterior->sp_web_url)->toBe('https://sp/obsoletos/legacy-1')
        ->and($anterior->sp_folder_path)->toBe(OM_OBSOLETOS . '/Calidad/Formatos obsoletos')
        ->and($nueva->fresh()->sp_estado)->toBe('publicado');
});

test('no se mueven archivos fuera de la rama de vigentes, ya en Obsoletos, ni carpetas', function () {
    fakeArbolObsoletosManuales();
    [$doc, $anterior] = documentoConVersionLegacy();

    app(PublishDocumentoVersion::class)->moverArchivosAObsoletos($doc, ['fuera-1', 'ya-obsoleto-1', 'carpeta-1'], $anterior);

    Http::assertNotSent(fn ($r) => $r->method() === 'PATCH');
    expect($anterior->fresh()->sp_item_id)->toBeNull();
});

test('finalizar pasa al job de publicación los archivos marcados en el selector', function () {
    Queue::fake();

    $solicitud = SolicitudFormato::create([
        'user_id' => User::factory()->create()->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Formato de inspección',
        'tipo_documento' => 'Formato',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/ref.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'F-002',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
            'archivo_oficial' => UploadedFile::fake()->create('oficial.pdf', 100, 'application/pdf'),
            'sp_carpeta_vigente_path' => OM_RAIZ . '/Calidad/Formatos',
            'sp_obsoletos_items' => ['01ABCD!legacy-1', '01ABCD!legacy-1', 'legacy-2'],
        ])
        ->assertRedirect(route('solicitudes.index'));

    Queue::assertPushed(PublicarVersionEnSharePoint::class, fn ($job) => $job->itemsObsoletos === ['01ABCD!legacy-1', 'legacy-2']);
});

test('un ID de SharePoint con caracteres no válidos se rechaza', function () {
    Queue::fake();

    $solicitud = SolicitudFormato::create([
        'user_id' => User::factory()->create()->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Formato de inspección',
        'tipo_documento' => 'Formato',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/ref.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'F-003',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
            'sp_obsoletos_items' => ['../../root'],
        ])
        ->assertSessionHasErrors('sp_obsoletos_items.0');

    Queue::assertNothingPushed();
});

test('atender una baja por solicitud mueve a Obsoletos las versiones publicadas y los archivos marcados', function () {
    Queue::fake();

    $doc = Documento::create(['codigo' => 'F-009', 'nombre' => 'A dar de baja', 'area' => 'Calidad', 'tipo_documento' => 'Formato', 'estatus' => 'vigente']);
    $publicada = DocumentoVersion::create(['documento_id' => $doc->id, 'version' => 'VER-1', 'estatus' => 'obsoleto', 'revision_actual' => '0', 'sp_item_id' => 'item-publicado']);
    $vigente = DocumentoVersion::create(['documento_id' => $doc->id, 'version' => 'VER-2', 'estatus' => 'vigente', 'revision_actual' => '0']);
    $doc->update(['version_vigente_id' => $vigente->id]);

    $solicitud = SolicitudFormato::create([
        'user_id' => User::factory()->create()->id,
        'accion' => 'baja',
        'estado' => 'aprobado_jefe',
        'documento_id' => $doc->id,
        'motivo_baja' => 'Ya no aplica',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'sp_obsoletos_items' => ['legacy-1'],
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($doc->fresh()->estatus)->toBe('baja');

    Queue::assertPushed(MoverVersionASharePointObsoletos::class, 1);
    Queue::assertPushed(MoverArchivosSharePointAObsoletos::class, fn ($job) => $job->itemIds === ['legacy-1'] && $job->versionALigarId === $vigente->id);
});

test('atender sin archivo nuevo retira a Obsoletos el archivo de la versión anterior', function () {
    Queue::fake();

    $doc = Documento::create(['codigo' => 'F-010', 'nombre' => 'Sin archivo nuevo', 'area' => 'Calidad', 'tipo_documento' => 'Formato', 'estatus' => 'vigente']);
    $anterior = DocumentoVersion::create(['documento_id' => $doc->id, 'version' => 'VER-1', 'estatus' => 'vigente', 'revision_actual' => '0', 'sp_item_id' => 'item-anterior']);
    $doc->update(['version_vigente_id' => $anterior->id]);

    $solicitud = SolicitudFormato::create([
        'user_id' => User::factory()->create()->id,
        'accion' => 'actualizacion',
        'estado' => 'aprobado_jefe',
        'documento_id' => $doc->id,
        'nombre_documento' => 'Sin archivo nuevo',
    ]);

    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/ref.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'F-010',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
        ])
        ->assertRedirect(route('solicitudes.index'));

    Queue::assertNotPushed(PublicarVersionEnSharePoint::class);
    Queue::assertPushed(MoverVersionASharePointObsoletos::class, fn ($job) => true);
});

test('el selector regresa carpetas y documentos, y con ?ruta= abre directo en esa carpeta', function () {
    fakeArbolObsoletosManuales();

    $this->actingAs($this->adminSgi)
        ->getJson(route('sharepoint.carpetas', ['ruta' => OM_RAIZ . '/Calidad/Formatos']))
        ->assertOk()
        ->assertJsonPath('current_id', 'id-formatos')
        ->assertJsonPath('breadcrumb.0.name', 'Calidad')
        ->assertJsonPath('breadcrumb.1.name', 'Formatos')
        ->assertJsonPath('folders.0.name', 'Anexos')
        ->assertJsonPath('files.0.id', 'legacy-1')
        ->assertJsonPath('files.0.modified_at', '2024-03-11T10:00:00Z');
});

test('el selector de SharePoint es solo para administrador_sgi', function () {
    $this->actingAs(User::factory()->create())
        ->getJson(route('sharepoint.carpetas'))
        ->assertForbidden();
});

test('un job de publicación encolado antes de existir itemsObsoletos se reconstruye sin tronar', function () {
    // unserialize no llama al constructor: simula un job viejo que no traía la propiedad.
    $job = (new ReflectionClass(PublicarVersionEnSharePoint::class))->newInstanceWithoutConstructor();

    expect($job->itemsObsoletos)->toBe([]);
});
