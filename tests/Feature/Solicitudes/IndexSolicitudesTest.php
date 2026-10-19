<?php

use App\Models\SolicitudFormato;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);
});

test('el listado muestra documento, jefe revisor y resumen por estado', function () {
    $jefe = User::factory()->create(['name' => 'Jefa Revisora']);
    $jefe->assignRole('jefe');

    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'jefe_id' => $jefe->id,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Manual de calidad',
    ]);

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'jefe_id' => $jefe->id,
        'accion' => 'baja',
        'estado' => 'atendido',
        'nombre_documento' => 'Formato viejo',
    ]);

    $this->actingAs($usuario)
        ->get(route('solicitudes.index'))
        ->assertOk()
        ->assertViewHas('resumen', fn ($r) => $r['total'] === 2 && $r['pendientes'] === 1 && $r['atendidas'] === 1)
        ->assertSee('Manual de calidad')
        ->assertSee('Jefa Revisora');
});

test('se puede filtrar por tipo de acción y buscar por nombre de documento', function () {
    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Manual de calidad',
    ]);

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'accion' => 'baja',
        'estado' => 'pendiente',
        'nombre_documento' => 'Formato viejo',
    ]);

    $this->actingAs($usuario)
        ->get(route('solicitudes.index', ['accion' => 'baja']))
        ->assertOk()
        ->assertSee('Formato viejo')
        ->assertDontSee('Manual de calidad');

    $this->actingAs($usuario)
        ->get(route('solicitudes.index', ['nombre' => 'manual']))
        ->assertOk()
        ->assertSee('Manual de calidad')
        ->assertDontSee('Formato viejo');
});

test('el filtro "hasta" incluye las solicitudes creadas ese mismo día', function () {
    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'accion' => 'nuevo_documento',
        'estado' => 'pendiente',
        'nombre_documento' => 'Creada hoy',
    ]);

    $this->actingAs($usuario)
        ->get(route('solicitudes.index', ['hasta' => now()->toDateString()]))
        ->assertOk()
        ->assertSee('Creada hoy');
});
