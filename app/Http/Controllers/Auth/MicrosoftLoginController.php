<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        /** @var SocialiteUser $cuentaMicrosoft */
        $cuentaMicrosoft = Socialite::driver('microsoft')->user();

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
        $user = User::where('microsoft_id', $cuentaMicrosoft->getId())
            ->orWhere('email', $cuentaMicrosoft->getEmail())
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
