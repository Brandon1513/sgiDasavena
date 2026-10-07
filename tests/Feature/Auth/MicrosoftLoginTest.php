<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider as SocialiteProviderContract;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);
});

function mockearCuentaMicrosoft(string $id, string $email, string $name, ?string $tid = null): void
{
    $cuenta = Mockery::mock(SocialiteUserContract::class);
    $cuenta->shouldReceive('getId')->andReturn($id);
    $cuenta->shouldReceive('getEmail')->andReturn($email);
    $cuenta->shouldReceive('getName')->andReturn($name);

    $claims = (object) ['tid' => $tid ?? config('services.microsoft.tenant')];

    $provider = Mockery::mock(SocialiteProviderContract::class);
    $provider->shouldReceive('user')->andReturn($cuenta);
    $provider->shouldReceive('getClaims')->andReturn($claims);

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

test('un token de un tenant distinto al configurado es rechazado', function () {
    mockearCuentaMicrosoft('ms-999', 'intruso@otraempresa.com', 'Cuenta Ajena', tid: 'tenant-ajeno');

    $this->get(route('login.microsoft.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::where('email', 'intruso@otraempresa.com')->exists())->toBeFalse();
});

test('un correo que no es del dominio corporativo no se usa para enlazar cuentas', function () {
    $cuentaExistente = User::factory()->create([
        'email' => 'cliente@proveedor.com',
        'microsoft_id' => 'ms-original',
    ]);
    $cuentaExistente->assignRole('usuario');

    // Mismo microsoft_id: debe entrar a la MISMA cuenta sin depender del
    // correo (el enlace por correo solo aplica a dominios @dasavena.com).
    mockearCuentaMicrosoft('ms-original', 'cliente@proveedor.com', 'Cliente');

    $this->get(route('login.microsoft.callback'))
        ->assertRedirect(route('dashboard'));

    $cuentaExistente->refresh();
    expect(User::where('email', 'cliente@proveedor.com')->count())->toBe(1)
        ->and($cuentaExistente->microsoft_id)->toBe('ms-original');
});
