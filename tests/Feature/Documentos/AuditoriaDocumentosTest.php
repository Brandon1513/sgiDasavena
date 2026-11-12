<?php

use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

test('dar de baja un documento deja en la auditoría quién lo hizo y qué versiones cambió', function () {
    Role::firstOrCreate(['name' => 'administrador_sgi']);
    $admin = User::factory()->create();
    $admin->assignRole('administrador_sgi');

    $doc = Documento::create(['codigo' => 'P-AUD-01', 'nombre' => 'Prueba', 'area' => 'Calidad', 'estatus' => 'vigente']);
    $version = DocumentoVersion::create([
        'documento_id'    => $doc->id,
        'version'         => '1',
        'revision_actual' => '0',
        'fecha_version'   => now(),
        'estatus'         => 'vigente',
    ]);

    $this->actingAs($admin)->post(route('documentos.baja', $doc->id))->assertRedirect();

    $resumen = Activity::where('description', 'Baja de documento')->first();

    expect($resumen)->not->toBeNull()
        ->and($resumen->causer_id)->toBe($admin->id)
        ->and($resumen->subject_id)->toBe($doc->id);

    expect(Activity::where('subject_type', DocumentoVersion::class)
        ->where('subject_id', $version->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('el administrador_sgi ve el historial de un documento con sus cambios', function () {
    Role::firstOrCreate(['name' => 'administrador_sgi']);
    $admin = User::factory()->create();
    $admin->assignRole('administrador_sgi');

    $doc = Documento::create(['codigo' => 'P-HIS-01', 'nombre' => 'Prueba', 'area' => 'Calidad', 'estatus' => 'vigente']);
    $doc->update(['nombre' => 'Prueba corregida']);

    $this->actingAs($admin)
        ->get(route('documentos.historial', $doc))
        ->assertOk()
        ->assertSee('Modificación: Documento')
        ->assertSee('Prueba corregida');
});

test('un usuario que no es administrador_sgi no puede ver el historial', function () {
    Role::firstOrCreate(['name' => 'usuario']);
    $usuario = User::factory()->create(['area' => 'Calidad']);
    $usuario->assignRole('usuario');

    $doc = Documento::create(['codigo' => 'P-HIS-02', 'nombre' => 'Prueba', 'area' => 'Calidad', 'estatus' => 'vigente']);

    $this->actingAs($usuario)
        ->get(route('documentos.historial', $doc))
        ->assertForbidden();
});