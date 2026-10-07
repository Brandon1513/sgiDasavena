<?php

use App\Models\SolicitudFormato;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);
});

test('una solicitud ya aprobada o rechazada por el jefe no puede volver a decidirse', function () {
    $jefe = User::factory()->create();
    $jefe->assignRole('jefe');

    $usuario = User::factory()->create(['jefe_id' => $jefe->id]);
    $usuario->assignRole('usuario');

    $solicitud = SolicitudFormato::create([
        'user_id' => $usuario->id,
        'jefe_id' => $jefe->id,
        'accion' => 'nuevo_documento',
        'estado' => 'aprobado_jefe',
        'nombre_documento' => 'Doc de prueba',
    ]);

    $this->actingAs($jefe)
        ->post(route('solicitudes.approve_or_reject', $solicitud), [
            'decision' => 'rechazado_jefe',
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($solicitud->fresh()->estado)->toBe('aprobado_jefe');
});

test('una solicitud ya atendida por SGI no puede volver a finalizarse', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador_sgi');

    $usuario = User::factory()->create();

    $solicitud = SolicitudFormato::create([
        'user_id' => $usuario->id,
        'accion' => 'nuevo_documento',
        'estado' => 'atendido',
        'nombre_documento' => 'Doc de prueba',
        'codigo_documento' => 'DOC-EST-001',
    ]);

    $this->actingAs($admin)
        ->post(route('solicitudes.finalize', $solicitud), [
            'accion' => 'atender',
            'tipo_cambio' => 'version',
            'liga_archivo' => 'https://example.com/doc.pdf',
            'fecha_alta_sgi' => now()->toDateString(),
            'codigo_documento' => 'DOC-EST-001',
            'fecha_version' => now()->toDateString(),
            'vigencia_version_dias' => 365,
        ]);

    expect(\App\Models\Documento::where('codigo', 'DOC-EST-001')->exists())->toBeFalse();
});
