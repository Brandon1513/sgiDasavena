<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftLoginController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $tenantEsperado = config('services.microsoft.tenant');

        // Sin un tenant específico configurado, el proveedor cae a "common" y
        // dejaría entrar cualquier cuenta Microsoft (personal o de otra
        // empresa). Preferimos bloquear el login a arriesgar una entrada no
        // autorizada por una configuración incompleta.
        if (blank($tenantEsperado) || $tenantEsperado === 'common') {
            Log::error('Login con Microsoft bloqueado: MS_TENANT_ID no está configurado.');

            return redirect()->route('login')->withErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        $driver = Socialite::driver('microsoft');

        /** @var SocialiteUser $cuentaMicrosoft */
        $cuentaMicrosoft = $driver->user();

        $tid = $driver->getClaims()?->tid ?? null;

        if ($tid !== $tenantEsperado) {
            Log::warning('Login con Microsoft rechazado: el token pertenece a otro tenant.', [
                'tid' => $tid,
                'email' => $cuentaMicrosoft->getEmail(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = $this->encontrarOCrearUsuario($cuentaMicrosoft);

        if (! $user->activo) {
            return redirect()->route('login')->withErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function encontrarOCrearUsuario(SocialiteUser $cuentaMicrosoft): User
    {
        $email = $cuentaMicrosoft->getEmail();

        // Solo se vincula por correo cuando es un dominio corporativo propio:
        // de lo contrario una cuenta externa cuyo correo coincidiera con el
        // de un usuario de Dasavena podría quedar enlazada a esa cuenta.
        $esCorreoCorporativo = $email && str_ends_with(strtolower($email), '@dasavena.com');

        $user = User::where('microsoft_id', $cuentaMicrosoft->getId())
            ->when($esCorreoCorporativo, fn ($q) => $q->orWhere('email', $email))
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $cuentaMicrosoft->getName() ?: $cuentaMicrosoft->getEmail(),
                'email' => $cuentaMicrosoft->getEmail(),
                'microsoft_id' => $cuentaMicrosoft->getId(),
                // El login local sigue activo, así que se necesita alguna contraseña;
                // esta es aleatoria e inutilizable, el usuario siempre entrará por Microsoft.
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'activo' => true,
            ]);

            $user->assignRole('usuario');

            return $user;
        }

        // Microsoft ya verificó su identidad corporativa; si la cuenta venía
        // de un registro local sin verificar (o sin microsoft_id todavía),
        // se completa aquí para no dejarla bloqueada por el middleware
        // "verified" en adelante.
        $user->microsoft_id ??= $cuentaMicrosoft->getId();
        $user->email_verified_at ??= now();
        $user->save();

        return $user;
    }
}
