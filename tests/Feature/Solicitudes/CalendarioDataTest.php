<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrador_sgi');
});

test('un parámetro month inválido no tumba el calendario, cae al mes actual', function () {
    $this->actingAs($this->admin)
        ->get(route('solicitudes.calendar.data', ['month' => 'no-es-una-fecha']))
        ->assertOk()
        ->assertJson(['month' => now()->format('Y-m')]);
});
