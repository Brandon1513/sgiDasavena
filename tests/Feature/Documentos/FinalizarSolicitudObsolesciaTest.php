<?php

use App\Models\Documento;
use App\Models\DocumentoRevision;
use App\Models\DocumentoVersion;
use App\Models\SolicitudFormato;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    $this->adminSgi = User::factory()->create();
    $this->adminSgi->assignRole('administrador_sgi');

    $this->documento = Documento::create([
        'codigo' => 'DOC-TEST-001',
        'nombre' => 'Documento de prueba',
        'area' => 'TI',
        'estatus' => 'vigente',
    ]);

    $this->versionVieja = DocumentoVersion::create([
        'documento_id' => $this->documento->id,
        'version' => 'VER-OLD',
        'revision_actual' => 'A',
        'fecha_version' => now()->subYear()->toDateString(),
        'estatus' => 'vigente',
    ]);

    $this->documento->update(['version_vigente_id' => $this->versionVieja->id]);

    $this->revisionVieja = DocumentoRevision::create([
        'documento_version_id' => $this->versionVieja->id,
        'revision_actual' => 'A',
        'fecha_revision' => now()->subMonths(6)->toDateString(),
        'estatus' => 'vigente',
    ]);

    $this->solicitante = User::factory()->create();
    $this->solicitud = SolicitudFormato::create([
        'user_id' => $this->solicitante->id,
        'accion' => 'actualizacion',
        'estado' => 'aprobado_jefe',
        'documento_id' => $this->documento->id,
    ]);
});

test('al publicar una nueva versión, la versión y la revisión viejas quedan obsoletas', function () {
    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $this->solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/doc.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'DOC-TEST-001',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($this->versionVieja->fresh()->estatus)->toBe('obsoleto')
        ->and($this->revisionVieja->fresh()->estatus)->toBe('obsoleta');

    $nuevaVersion = $this->documento->fresh()->versionVigente;
    expect($nuevaVersion->id)->not->toBe($this->versionVieja->id)
        ->and($nuevaVersion->estatus)->toBe('vigente');
});

test('al publicar una nueva revisión, la revisión y versión viejas también quedan obsoletas', function () {
    $this->actingAs($this->adminSgi)
        ->post(route('solicitudes.finalize', $this->solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'revision',
            'liga_archivo' => 'https://example.com/doc.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'DOC-TEST-001',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
            'revision_actual' => 'B',
            'vigencia_revision_dias' => 180,
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($this->versionVieja->fresh()->estatus)->toBe('obsoleto')
        ->and($this->revisionVieja->fresh()->estatus)->toBe('obsoleta');

    $nuevaVersion = $this->documento->fresh()->versionVigente;
    $nuevaRevision = $nuevaVersion->revisiones()->where('estatus', 'vigente')->first();
    expect($nuevaRevision)->not->toBeNull()
        ->and($nuevaRevision->revision_actual)->toBe('B');
});
