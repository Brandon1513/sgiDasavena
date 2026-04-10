<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');

    .nav-root {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
    }

    /* ── Navbar principal ── */
    .nav-root nav {
        background: rgba(250, 247, 251, 0.92);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(106, 44, 117, 0.12);
        box-shadow: 0 2px 20px rgba(106, 44, 117, 0.07);
        position: relative;
        z-index: 50;
    }

    /* Línea dorada superior */
    .nav-root nav::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, #6A2C75, #D4A018, #6A2C75);
    }

    /* ── Logo ── */
    .nav-logo {
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .nav-logo:hover { transform: scale(1.04); opacity: 0.85; }

    /* ── Botón activo Inicio ── */
    .nav-link-active {
        background: linear-gradient(135deg, #6A2C75, #8e3d9e) !important;
        color: #fff !important;
        box-shadow: 0 3px 12px rgba(106, 44, 117, 0.3);
    }

    /* ── Botones dropdown ── */
    .nav-dropdown-btn {
        display: inline-flex;
        align-items: center;
        padding: 7px 16px;
        font-size: 0.82rem;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #4a2a55;
        background: rgba(106, 44, 117, 0.06);
        border: 1px solid rgba(106, 44, 117, 0.18);
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        gap: 6px;
    }
    .nav-dropdown-btn:hover {
        background: rgba(106, 44, 117, 0.12);
        border-color: rgba(106, 44, 117, 0.35);
        box-shadow: 0 3px 12px rgba(106, 44, 117, 0.12);
        color: #6A2C75;
    }
    .nav-dropdown-btn svg { transition: transform 0.25s ease; }
    .nav-dropdown-btn:focus svg { transform: rotate(180deg); }

    /* ── Nav link normal ── */
    .nav-link-base {
        display: inline-flex;
        align-items: center;
        padding: 7px 16px;
        font-size: 0.82rem;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #4a2a55;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .nav-link-base:hover {
        background: rgba(106, 44, 117, 0.06);
        border-color: rgba(106, 44, 117, 0.15);
        color: #6A2C75;
    }

    /* ── Avatar / Perfil ── */
    .nav-avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6A2C75, #D4A018);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(106,44,117,0.3);
    }

    /* ── Botón perfil ── */
    .nav-profile-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px 6px 8px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        color: #4a2a55;
        background: rgba(106, 44, 117, 0.06);
        border: 1px solid rgba(106, 44, 117, 0.18);
        border-radius: 100px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .nav-profile-btn:hover {
        background: rgba(106, 44, 117, 0.12);
        border-color: rgba(106, 44, 117, 0.35);
        box-shadow: 0 3px 12px rgba(106, 44, 117, 0.12);
    }

    /* ── Dropdown content override ── */
    .nav-dropdown-item {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.82rem;
        color: #4a2a55;
    }
    .nav-dropdown-item:hover { background: rgba(106, 44, 117, 0.07) !important; color: #6A2C75; }
    .nav-dropdown-item-danger { color: #c0392b !important; }
    .nav-dropdown-item-danger:hover { background: rgba(192, 57, 43, 0.06) !important; }

    /* ── Hamburger ── */
    .nav-hamburger {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        color: #6A2C75;
        border: 1px solid rgba(106, 44, 117, 0.2);
        border-radius: 8px;
        background: rgba(106, 44, 117, 0.05);
        transition: all 0.2s ease;
    }
    .nav-hamburger:hover { background: rgba(106, 44, 117, 0.12); }

    /* ── Mobile menu ── */
    .nav-mobile {
        background: rgba(250, 247, 251, 0.98);
        border-top: 1px solid rgba(106, 44, 117, 0.1);
    }
    .nav-mobile-link {
        display: block;
        padding: 9px 14px;
        border-radius: 8px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.83rem;
        font-weight: 600;
        color: #4a2a55;
        text-decoration: none;
        transition: all 0.2s;
    }
    .nav-mobile-link:hover { background: rgba(106, 44, 117, 0.08); color: #6A2C75; }
    .nav-mobile-link-active {
        background: linear-gradient(135deg, #6A2C75, #8e3d9e);
        color: #fff !important;
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
        border-radius: 8px;
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
                <div class="hidden sm:block w-px h-6 bg-gradient-to-b from-transparent via-[#6A2C75]/25 to-transparent"></div>

                <!-- Navigation Links -->
                @if (Auth::check())
                <div class="hidden gap-1 sm:flex sm:items-center">

                    <!-- Inicio -->
                    <a href="{{ route('dashboard') }}"
                        class="nav-link-base {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('Inicio') }}
                    </a>

                    <!-- Menú Administración -->
                    <x-dropdown align="left" width="52">
                        <x-slot name="trigger">
                            @role('administrador')
                            <button class="nav-dropdown-btn">
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Administración
                                <svg class="w-3.5 h-3.5 opacity-50" fill="currentColor" viewBox="0 0 20 20">
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

                    <!-- Menú SGI -->
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="nav-dropdown-btn">
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                SGI
                                <svg class="w-3.5 h-3.5 opacity-50" fill="currentColor" viewBox="0 0 20 20">
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
                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button class="nav-profile-btn">
                            <div class="nav-avatar">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 opacity-40" fill="currentColor" viewBox="0 0 20 20">
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
                <button @click="open = !open" class="nav-hamburger">
                    <svg class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Mobile Menu ── -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden nav-mobile">
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