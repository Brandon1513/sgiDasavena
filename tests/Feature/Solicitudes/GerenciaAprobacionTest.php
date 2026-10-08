<?php

use App\Models\SolicitudFormato;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);
});

test('un usuario de gerencia puede auto-aprobar su propia solicitud', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('gerencia');

    $solicitud = SolicitudFormato::create([
        'user_id' => $gerente->id,
        'jefe_id' => null,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Doc de gerencia',
    ]);

    $this->actingAs($gerente)
        ->get(route('solicitudes.approval_form', $solicitud))
        ->assertOk();

    $this->actingAs($gerente)
        ->post(route('solicitudes.approve_or_reject', $solicitud), [
            'decision' => 'aprobado_jefe',
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($solicitud->fresh()->estado)->toBe('aprobado_jefe');
});

test('un usuario de gerencia puede aprobar la solicitud de alguien que lo tiene asignado como jefe', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('gerencia');

    $subordinado = User::factory()->create(['jefe_id' => $gerente->id]);
    $subordinado->assignRole('usuario');

    $solicitud = SolicitudFormato::create([
        'user_id' => $subordinado->id,
        'jefe_id' => $gerente->id,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Doc de subordinado',
    ]);

    $this->actingAs($gerente)
        ->post(route('solicitudes.approve_or_reject', $solicitud), [
            'decision' => 'aprobado_jefe',
        ])
        ->assertRedirect(route('solicitudes.index'));

    expect($solicitud->fresh()->estado)->toBe('aprobado_jefe');
});

test('un usuario de gerencia no puede aprobar una solicitud donde no es el jefe asignado', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('gerencia');

    $otroJefe = User::factory()->create();
    $solicitante = User::factory()->create(['jefe_id' => $otroJefe->id]);

    $solicitud = SolicitudFormato::create([
        'user_id' => $solicitante->id,
        'jefe_id' => $otroJefe->id,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Doc ajeno',
    ]);

    $this->actingAs($gerente)
        ->get(route('solicitudes.approval_form', $solicitud))
        ->assertForbidden();

    expect($solicitud->fresh()->estado)->toBe('pendiente');
});

test('un usuario de gerencia no puede finalizar solicitudes, solo administrador_sgi', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('gerencia');

    $solicitud = SolicitudFormato::create([
        'user_id' => $gerente->id,
        'estado' => 'aprobado_jefe',
        'accion' => 'nuevo_documento',
        'nombre_documento' => 'Doc de gerencia',
    ]);

    $this->actingAs($gerente)
        ->get(route('solicitudes.finalize_form', $solicitud))
        ->assertForbidden();

    $this->actingAs($gerente)
        ->post(route('solicitudes.finalize', $solicitud), ['accion' => 'atender'])
        ->assertForbidden();
});
