<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');

    .nav-root {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
    }

    /* Navbar principal */
    .nav-root nav {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(16px) saturate(160%);
        -webkit-backdrop-filter: blur(16px) saturate(160%);
        border-bottom: 1px solid rgba(106, 44, 117, 0.08);
        position: sticky;
        top: 0;
        z-index: 50;
        transition: box-shadow .25s ease;
    }

    /* Linea dorada superior, muy fina */
    .nav-root nav::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, #6A2C75, #D4A018, #6A2C75);
        background-size: 200% 100%;
        animation: nav-gleam 6s linear infinite;
    }
    @keyframes nav-gleam {
        0%   { background-position: 0% 0; }
        100% { background-position: 200% 0; }
    }

    /* Logo */
    .nav-logo {
        transition: transform .35s cubic-bezier(.34,1.56,.64,1);
    }
    .nav-logo:hover { transform: scale(1.06) rotate(-3deg); }
    .nav-logo:active { transform: scale(.96); }

    /* Item de navegacion unificado (links + triggers de dropdown) */
    .nav-item {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        font-size: 0.8rem;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-weight: 600;
        letter-spacing: 0.03em;
        color: #5b3a63;
        background: transparent;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        cursor: pointer;
        transition: color .2s ease, background-color .2s ease, transform .15s ease;
    }
    .nav-item::after {
        content: '';
        position: absolute;
        left: 14px; right: 14px; bottom: 3px;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, #6A2C75, #D4A018);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .3s cubic-bezier(.4,0,.2,1);
    }
    .nav-item:hover {
        color: #6A2C75;
        background: rgba(106, 44, 117, 0.05);
    }
    .nav-item:hover::after { transform: scaleX(1); }
    .nav-item:active { transform: scale(.95); }

    .nav-item-active {
        color: #6A2C75 !important;
        background: rgba(106, 44, 117, 0.07);
    }
    .nav-item-active::after { transform: scaleX(1) !important; }

    .nav-item svg.nav-chevron {
        transition: transform .25s ease;
        opacity: .55;
    }

    /* Avatar / Perfil */
    .nav-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6A2C75, #D4A018);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.9), 0 2px 8px rgba(106,44,117,0.25);
        transition: box-shadow .2s ease;
    }

    /* Boton perfil */
    .nav-profile-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 14px 5px 6px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4a2a55;
        background: rgba(106, 44, 117, 0.04);
        border: 1px solid rgba(106, 44, 117, 0.12);
        border-radius: 100px;
        transition: all .2s ease;
        cursor: pointer;
    }
    .nav-profile-btn:hover {
        background: rgba(106, 44, 117, 0.09);
        border-color: rgba(106, 44, 117, 0.28);
    }
    .nav-profile-btn:hover .nav-avatar { box-shadow: 0 0 0 2px rgba(255,255,255,0.9), 0 0 0 4px rgba(212,160,24,0.3); }
    .nav-profile-btn:active { transform: scale(.96); }

    /* Dropdown content override */
    .nav-dropdown-item {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.82rem;
        color: #4a2a55;
        transition: background-color .15s ease, color .15s ease, padding-left .15s ease;
    }
    .nav-dropdown-item:hover { background: rgba(106, 44, 117, 0.06) !important; color: #6A2C75; padding-left: 1.15rem !important; }
    .nav-dropdown-item-danger { color: #c0392b !important; }
    .nav-dropdown-item-danger:hover { background: rgba(192, 57, 43, 0.06) !important; padding-left: 1.15rem !important; }

    /* Hamburger animado (3 lineas a X) */
    .nav-hamburger {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px; height: 34px;
        color: #6A2C75;
        border: 1px solid rgba(106, 44, 117, 0.15);
        border-radius: 9px;
        background: rgba(106, 44, 117, 0.04);
        transition: background-color .2s ease, transform .15s ease;
    }
    .nav-hamburger:hover { background: rgba(106, 44, 117, 0.1); }
    .nav-hamburger:active { transform: scale(.92); }
    .hamburger-box { position: relative; width: 16px; height: 12px; }
    .hamburger-line {
        position: absolute; left: 0; width: 16px; height: 2px;
        border-radius: 2px; background: currentColor;
        transition: transform .3s cubic-bezier(.4,0,.2,1), opacity .2s ease, top .3s cubic-bezier(.4,0,.2,1);
    }
    .hamburger-line:nth-child(1) { top: 0; }
    .hamburger-line:nth-child(2) { top: 5px; }
    .hamburger-line:nth-child(3) { top: 10px; }
    .hamburger-line-1-open { top: 5px; transform: rotate(45deg); }
    .hamburger-line-2-open { opacity: 0; transform: scaleX(0); }
    .hamburger-line-3-open { top: 5px; transform: rotate(-45deg); }

    /* Mobile menu (colapso animado, sin display:none abrupto) */
    .nav-mobile {
        background: rgba(255, 255, 255, 0.98);
        border-top: 1px solid rgba(106, 44, 117, 0.08);
        transition: max-height .32s cubic-bezier(.4,0,.2,1), opacity .22s ease;
    }
    .nav-mobile-link {
        position: relative;
        display: block;
        padding: 9px 14px 9px 18px;
        border-radius: 8px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.83rem;
        font-weight: 600;
        color: #4a2a55;
        text-decoration: none;
        border-left: 2px solid transparent;
        transition: background-color .18s ease, color .18s ease, border-color .18s ease, padding-left .18s ease;
    }
    .nav-mobile-link:hover { background: rgba(106, 44, 117, 0.07); color: #6A2C75; padding-left: 22px; }
    .nav-mobile-link-active {
        background: rgba(106, 44, 117, 0.08);
        border-left-color: #D4A018;
        color: #6A2C75 !important;
    }

    /* Login button */
    .nav-btn-login {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 22px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: #fff;
        background: linear-gradient(135deg, #6A2C75, #8e3d9e);
        border-radius: 100px;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 3px 14px rgba(106, 44, 117, 0.28);
    }
    .nav-btn-login:hover {
        background: linear-gradient(135deg, #D4A018, #f0c84a);
        color: #2d1033;
        box-shadow: 0 5px 18px rgba(212, 160, 24, 0.35);
        transform: translateY(-1px);
    }
    .nav-btn-login:active { transform: translateY(0) scale(.96); }
    .nav-btn-login svg { transition: transform .25s ease; }
    .nav-btn-login:hover svg { transform: translateX(3px); }

    /* Divider dorado en dropdown */
    .nav-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(212,160,24,0.4), transparent);
        margin: 4px 8px;
    }
</style>

<div class="nav-root">
<nav x-data="{ open: false }">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- IZQUIERDA: Logo + Links -->
            <div class="flex items-center gap-6">

                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}" class="nav-logo">
                        <x-application-logo class="block w-auto h-9 text-[#6A2C75] fill-current" />
                    </a>
                </div>

                <!-- Separator visual -->
                <div class="hidden sm:block w-px h-6 bg-gradient-to-b from-transparent via-[#6A2C75]/20 to-transparent"></div>

                <!-- Navigation Links -->
                @if (Auth::check())
                <div class="hidden gap-1 sm:flex sm:items-center">

                    <!-- Inicio -->
                    <a href="{{ route('dashboard') }}"
                        class="nav-item {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('Inicio') }}
                    </a>

                    <!-- Menu Administracion (rol administrador) -->
                    <x-dropdown align="left" width="52" contentClasses="py-1.5 bg-white">
                        <x-slot name="trigger">
                            @role('administrador')
                            <button class="nav-item" type="button">
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Administración
                                <svg class="w-3.5 h-3.5 nav-chevron" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            @endrole
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('usuarios.index')" class="nav-dropdown-item">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4A018]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Usuarios') }}
                                </div>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Menu SGI -->
                    <x-dropdown align="left" width="56" contentClasses="py-1.5 bg-white">
                        <x-slot name="trigger">
                            <button class="nav-item" type="button">
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                SGI
                                <svg class="w-3.5 h-3.5 nav-chevron" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('solicitudes.index')" class="nav-dropdown-item">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4A018]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    {{ __('Solicitudes') }}
                                </div>
                            </x-dropdown-link>

                            @role('administrador_sgi')
                            <x-dropdown-link :href="route('solicitudes.calendar')" class="nav-dropdown-item">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4A018]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Calendario') }}
                                </div>
                            </x-dropdown-link>
                            @endrole

                            <x-dropdown-link :href="route('dashboard.user')" class="nav-dropdown-item">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4A018]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    {{ __('Documentación por área') }}
                                </div>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
                @endif
            </div>

            <!-- DERECHA: Perfil -->
            <div class="hidden sm:flex sm:items-center gap-3">
                @if (Auth::check())
                <x-dropdown align="right" width="52" contentClasses="py-1.5 bg-white">
                    <x-slot name="trigger">
                        <button class="nav-profile-btn" type="button">
                            <div class="nav-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 nav-chevron" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="nav-dropdown-item">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#6A2C75]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Perfil') }}
                            </div>
                        </x-dropdown-link>
                        <div class="nav-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="nav-dropdown-item nav-dropdown-item-danger">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('Cerrar Sesión') }}
                                </div>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                <a href="{{ route('login') }}" class="nav-btn-login">
                    Iniciar Sesión
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="nav-hamburger" type="button" :aria-expanded="open.toString()" aria-label="Abrir menú">
                    <span class="hamburger-box">
                        <span class="hamburger-line" :class="{ 'hamburger-line-1-open': open }"></span>
                        <span class="hamburger-line" :class="{ 'hamburger-line-2-open': open }"></span>
                        <span class="hamburger-line" :class="{ 'hamburger-line-3-open': open }"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="open ? 'max-h-[520px] opacity-100' : 'max-h-0 opacity-0'"
        class="overflow-hidden sm:hidden nav-mobile">
        <div class="px-4 pt-3 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}"
                class="nav-mobile-link {{ request()->routeIs('dashboard') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Inicio') }}
            </a>
            @role('administrador')
            <a href="{{ route('usuarios.index') }}"
                class="nav-mobile-link {{ request()->routeIs('usuarios.index') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Usuarios') }}
            </a>
            @endrole
            <a href="{{ route('solicitudes.index') }}"
                class="nav-mobile-link {{ request()->routeIs('solicitudes.index') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Solicitudes') }}
            </a>
            @role('administrador_sgi')
            <a href="{{ route('solicitudes.calendar') }}"
                class="nav-mobile-link {{ request()->routeIs('solicitudes.calendar') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Calendario') }}
            </a>
            @endrole
            <a href="{{ route('dashboard.user') }}"
                class="nav-mobile-link {{ request()->routeIs('dashboard.user') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Documentación por área') }}
            </a>
        </div>

        @if (Auth::check())
        <div class="px-4 py-4 border-t border-[#6A2C75]/10">
            <div class="flex items-center gap-3 mb-3">
                <div class="nav-avatar w-9 h-9 text-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div>
                    <div class="text-sm font-semibold text-[#2d1033]">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-[#6A2C75]/60">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="nav-mobile-link">{{ __('Perfil') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="nav-mobile-link" style="color:#c0392b;">
                        {{ __('Cerrar Sesión') }}
                    </a>
                </form>
            </div>
        </div>
        @else
        <div class="px-4 py-3 border-t border-[#6A2C75]/10">
            <a href="{{ route('login') }}" class="nav-btn-login w-full justify-center">
                {{ __('Iniciar Sesión') }}
            </a>
        </div>
        @endif
    </div>
</nav>
</div>
