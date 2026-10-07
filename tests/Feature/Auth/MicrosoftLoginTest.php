<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider as SocialiteProviderContract;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);
});

function mockearCuentaMicrosoft(string $id, string $email, string $name): void
{
    $cuenta = Mockery::mock(SocialiteUserContract::class);
    $cuenta->shouldReceive('getId')->andReturn($id);
    $cuenta->shouldReceive('getEmail')->andReturn($email);
    $cuenta->shouldReceive('getName')->andReturn($name);

    $provider = Mockery::mock(SocialiteProviderContract::class);
    $provider->shouldReceive('user')->andReturn($cuenta);

    Socialite::shouldReceive('driver')->with('microsoft')->andReturn($provider);
}

test('un usuario nuevo que entra con Microsoft se crea con rol usuario y sin área', function () {
    mockearCuentaMicrosoft('ms-123', 'nuevo@dasavena.com', 'Empleado Nuevo');

    $this->get(route('login.microsoft.callback'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $user = User::where('email', 'nuevo@dasavena.com')->firstOrFail();

    expect($user->microsoft_id)->toBe('ms-123')
        ->and($user->hasRole('usuario'))->toBeTrue()
        ->and($user->area)->toBeNull()
        ->and($user->activo)->toBeTruthy();
});

test('un usuario existente conserva su rol y área al entrar con Microsoft', function () {
    $jefe = User::factory()->create([
        'email' => 'jefe@dasavena.com',
        'area' => 'Producción',
    ]);
    $jefe->assignRole('jefe');

    mockearCuentaMicrosoft('ms-456', 'jefe@dasavena.com', 'Jefe de Área');

    $this->get(route('login.microsoft.callback'))
        ->assertRedirect(route('dashboard'));

    $jefe->refresh();

    expect($jefe->microsoft_id)->toBe('ms-456')
        ->and($jefe->hasRole('jefe'))->toBeTrue()
        ->and($jefe->area)->toBe('Producción');
});

test('un usuario desactivado no puede iniciar sesión con Microsoft', function () {
    User::factory()->create([
        'email' => 'inactivo@dasavena.com',
        'activo' => false,
    ]);

    mockearCuentaMicrosoft('ms-789', 'inactivo@dasavena.com', 'Usuario Inactivo');

    $this->get(route('login.microsoft.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
