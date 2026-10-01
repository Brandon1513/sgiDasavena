<?php

use App\Models\SolicitudFormato;
use App\Models\User;

test('un usuario autenticado puede ver el dashboard sin solicitudes registradas', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Hola,', false)
        ->assertSee($usuario->name);
});

test('el dashboard muestra la bandeja de solicitudes recientes del usuario', function () {
    $usuario = User::factory()->create();

    SolicitudFormato::create([
        'user_id' => $usuario->id,
        'accion' => 'actualizacion',
        'estado' => 'pendiente',
    ]);

    $this->actingAs($usuario)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Solicitudes recientes')
        ->assertSee('Pendiente');
});

test('el dashboard de un jefe con solicitudes vencidas de SLA muestra la alerta', function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    $jefe = User::factory()->create();
    $jefe->assignRole('jefe');

    $solicitud = SolicitudFormato::create([
        'user_id' => $jefe->id,
        'jefe_id' => $jefe->id,
        'accion' => 'baja',
        'estado' => 'pendiente',
    ]);

    // created_at no es fillable y Eloquent lo pisa con "now()" al crear;
    // se recorre manualmente para simular una solicitud vieja fuera de SLA.
    $solicitud->forceFill(['created_at' => now()->subDays(5)])->save();

    $this->actingAs($jefe)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('jefe')
        ->assertSee('Vencidas SLA')
        ->assertSee('Urgentes SLA');
});
