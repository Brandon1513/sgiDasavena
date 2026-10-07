<?php

use App\Models\Documento;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    $this->docTi = Documento::create([
        'codigo' => 'DOC-TI-001',
        'nombre' => 'Documento de TI',
        'area' => 'TI',
        'estatus' => 'vigente',
    ]);

    $this->docRh = Documento::create([
        'codigo' => 'DOC-RH-001',
        'nombre' => 'Documento de RH',
        'area' => 'Recursos Humanos',
        'estatus' => 'vigente',
    ]);
});

test('un usuario sin área asignada no ve ningún documento en el listado', function () {
    $usuario = User::factory()->create(['area' => null]);
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('documentos.index'))
        ->assertOk()
        ->assertDontSee('DOC-TI-001')
        ->assertDontSee('DOC-RH-001');
});

test('un usuario sin área asignada no puede ver el detalle de un documento de otra área', function () {
    $usuario = User::factory()->create(['area' => null]);
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('documentos.show', $this->docTi))
        ->assertForbidden();
});

test('un usuario de un área no puede ver el detalle de un documento de otra área', function () {
    $usuario = User::factory()->create(['area' => 'Recursos Humanos']);
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('documentos.show', $this->docTi))
        ->assertForbidden();

    $this->actingAs($usuario)
        ->get(route('documentos.show', $this->docRh))
        ->assertOk();
});

test('un administrador_sgi ve documentos de cualquier área', function () {
    $admin = User::factory()->create(['area' => null]);
    $admin->assignRole('administrador_sgi');

    $this->actingAs($admin)
        ->get(route('documentos.show', $this->docTi))
        ->assertOk();
});
