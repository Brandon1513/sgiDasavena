<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');

    .nav-root {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        position: sticky;
        top: 0;
        z-index: 50;
        padding: 14px 16px 0;
    }

    /* Navbar principal — flotante, efecto "liquid glass" */
    .nav-root nav {
        position: relative;
        max-width: 1240px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.62);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 26px;
        box-shadow:
            0 10px 40px rgba(106, 44, 117, 0.16),
            0 2px 8px rgba(0, 0, 0, .05),
            inset 0 1px 0 rgba(255, 255, 255, .8),
            inset 0 -1px 0 rgba(106, 44, 117, .06);
        transition: box-shadow .25s ease, background-color .25s ease;
        isolation: isolate;
        --mx: 50%;
        --my: 0%;
    }
    /* Base: blur seguro en cualquier navegador */
    .nav-root nav {
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
    }
    /* Mejora progresiva: distorsión tipo vidrio líquido (si el navegador
       no soporta url() dentro de backdrop-filter, ignora esta regla entera
       y se queda con el blur de arriba — no rompe nada). */
    @supports (backdrop-filter: blur(1px)) {
        .nav-root nav {
            backdrop-filter: url(#liquid-glass-distortion) blur(14px) saturate(180%) brightness(1.04);
            -webkit-backdrop-filter: blur(14px) saturate(180%) brightness(1.04);
        }
    }

    /* Capa decorativa: brillo especular que sigue el mouse + destello ambiental */
    .nav-glass-fx {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        border-radius: inherit;
        overflow: hidden;
    }
    .nav-glass-fx::before {
        /* Brillo especular: sigue al cursor, como luz atravesando el vidrio */
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(
            220px 140px at var(--mx) var(--my),
            rgba(255, 255, 255, .55),
            rgba(255, 255, 255, .12) 45%,
            transparent 70%
        );
        opacity: 0;
        transition: opacity .4s ease;
    }
    .nav-root nav:hover .nav-glass-fx::before,
    .nav-root nav:focus-within .nav-glass-fx::before {
        opacity: 1;
    }
    .nav-glass-fx::after {
        /* Destello ambiental: un barrido diagonal lento, como luz moviéndose sobre el vidrio */
        content: '';
        position: absolute;
        inset: -40% -10%;
        background: linear-gradient(
            115deg,
            transparent 30%,
            rgba(255, 255, 255, .35) 45%,
            rgba(255, 255, 255, .05) 55%,
            transparent 70%
        );
        background-size: 250% 250%;
        animation: nav-liquid-sheen 9s ease-in-out infinite;
        mix-blend-mode: overlay;
    }
    @keyframes nav-liquid-sheen {
        0%, 100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .nav-glass-fx::after { animation: none; }
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
        border: 1px solid transparent;
        border-radius: 10px;
        text-decoration: none;
        cursor: pointer;
        overflow: hidden;
        transition: color .2s ease, background-color .2s ease, border-color .2s ease, transform .15s ease, box-shadow .2s ease;
    }
    .nav-item::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.65) 50%, transparent 75%);
        background-size: 250% 250%;
        background-position: -120% -120%;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: background-position .55s ease;
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
        background: rgba(255, 255, 255, .45);
        border-color: rgba(255, 255, 255, .6);
        box-shadow: 0 4px 14px rgba(106, 44, 117, .1), inset 0 1px 0 rgba(255,255,255,.7);
        backdrop-filter: blur(6px) saturate(160%);
        -webkit-backdrop-filter: blur(6px) saturate(160%);
    }
    .nav-item:hover::before { background-position: 120% 120%; }
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
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 14px 5px 6px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.8rem;
        font-weight: 600;
        color: #4a2a55;
        background: rgba(255, 255, 255, .35);
        backdrop-filter: blur(8px) saturate(160%);
        -webkit-backdrop-filter: blur(8px) saturate(160%);
        border: 1px solid rgba(255, 255, 255, .55);
        border-radius: 100px;
        overflow: hidden;
        transition: all .2s ease;
        cursor: pointer;
    }
    .nav-profile-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.6) 50%, transparent 75%);
        background-size: 250% 250%;
        background-position: -120% -120%;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: background-position .55s ease;
    }
    .nav-profile-btn:hover {
        background: rgba(255, 255, 255, .55);
        border-color: rgba(255, 255, 255, .75);
        box-shadow: 0 4px 14px rgba(106, 44, 117, .12), inset 0 1px 0 rgba(255,255,255,.8);
    }
    .nav-profile-btn:hover::before { background-position: 120% 120%; }
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
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px; height: 34px;
        color: #6A2C75;
        border: 1px solid rgba(255, 255, 255, .55);
        border-radius: 9px;
        background: rgba(255, 255, 255, .35);
        backdrop-filter: blur(8px) saturate(160%);
        -webkit-backdrop-filter: blur(8px) saturate(160%);
        overflow: hidden;
        transition: background-color .2s ease, border-color .2s ease, transform .15s ease;
    }
    .nav-hamburger::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.6) 50%, transparent 75%);
        background-size: 250% 250%;
        background-position: -120% -120%;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: background-position .55s ease;
    }
    .nav-hamburger:hover { background: rgba(255, 255, 255, .55); border-color: rgba(255, 255, 255, .75); }
    .nav-hamburger:hover::before { background-position: 120% 120%; }
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
        position: relative;
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
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 3px 14px rgba(106, 44, 117, 0.28);
    }
    .nav-btn-login::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.55) 50%, transparent 75%);
        background-size: 250% 250%;
        background-position: -120% -120%;
        pointer-events: none;
        transition: background-position .55s ease;
    }
    .nav-btn-login:hover::before { background-position: 120% 120%; }
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

    /* Botón icono (búsqueda / notificaciones) */
    .nav-icon-btn {
        position: relative;
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px;
        color: #6A2C75;
        border: 1px solid rgba(255, 255, 255, .55);
        border-radius: 50%;
        background: rgba(255, 255, 255, .35);
        backdrop-filter: blur(8px) saturate(160%);
        -webkit-backdrop-filter: blur(8px) saturate(160%);
        overflow: hidden;
        transition: background-color .2s ease, border-color .2s ease, transform .15s ease;
    }
    .nav-icon-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 25%, rgba(255,255,255,.6) 50%, transparent 75%);
        background-size: 250% 250%;
        background-position: -120% -120%;
        mix-blend-mode: overlay;
        pointer-events: none;
        transition: background-position .55s ease;
    }
    .nav-icon-btn:hover { background: rgba(255, 255, 255, .55); border-color: rgba(255, 255, 255, .75); }
    .nav-icon-btn:hover::before { background-position: 120% 120%; }
    .nav-icon-btn:active { transform: scale(.93); }

    /* Panel flotante compartido (búsqueda / notificaciones) */
    .nav-search-panel, .nav-notif-panel {
        position: absolute; right: 0; top: calc(100% + 10px);
        width: 340px; max-width: calc(100vw - 32px);
        background: rgba(255,255,255,.98);
        border: 1px solid rgba(106,44,117,.12);
        border-radius: 18px;
        box-shadow: 0 16px 48px rgba(106,44,117,.2);
        overflow: hidden;
        z-index: 60;
    }

    /* Búsqueda */
    .nav-search-input-wrap { display: flex; align-items: center; gap: 8px; padding: 12px 14px; border-bottom: 1px solid rgba(106,44,117,.08); }
    .nav-search-input { flex: 1; border: none; outline: none; font-size: .85rem; color: #2d1033; background: transparent; font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif; }
    .nav-search-input::placeholder { color: #a78ab0; }
    .nav-search-results { max-height: 320px; overflow-y: auto; padding: 6px; }
    .nav-search-result { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; padding: 9px 10px; border-radius: 10px; text-decoration: none; transition: background-color .15s ease; }
    .nav-search-result:hover { background: rgba(106,44,117,.06); }
    .nav-search-result-tag { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #b38600; background: rgba(212,160,24,.14); padding: 2px 7px; border-radius: 100px; }
    .nav-search-result-title { font-size: .82rem; font-weight: 700; color: #2d1033; width: 100%; }
    .nav-search-result-sub { font-size: .74rem; color: #8a7690; }
    .nav-search-empty { padding: 20px 12px; text-align: center; font-size: .8rem; color: #a78ab0; }

    /* Notificaciones */
    .nav-notif-dot {
        position: absolute; top: 4px; right: 4px; width: 8px; height: 8px; border-radius: 50%;
        background: #D4A018; border: 2px solid #fff;
    }
    .nav-notif-head { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border-bottom: 1px solid rgba(106,44,117,.08); font-size: .78rem; font-weight: 700; color: #2d1033; text-transform: uppercase; letter-spacing: .04em; }
    .nav-notif-markall { font-size: .68rem; font-weight: 700; color: #6A2C75; text-transform: none; letter-spacing: 0; background: none; border: none; cursor: pointer; }
    .nav-notif-markall:hover { text-decoration: underline; }
    .nav-notif-item { display: flex; align-items: flex-start; gap: 8px; padding: 11px 14px; text-decoration: none; border-bottom: 1px solid rgba(106,44,117,.05); transition: background-color .15s ease; }
    .nav-notif-item:hover { background: rgba(106,44,117,.05); }
    .nav-notif-item-dot { width: 6px; height: 6px; border-radius: 50%; background: #6A2C75; flex-shrink: 0; margin-top: 6px; }
    .nav-notif-item-body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .nav-notif-item-text { font-size: .8rem; color: #2d1033; line-height: 1.4; }
    .nav-notif-item-time { font-size: .68rem; color: #a78ab0; }
    .nav-notif-empty { padding: 24px 14px; text-align: center; font-size: .8rem; color: #a78ab0; }
</style>

<!-- Filtro SVG del efecto "liquid glass" (distorsión sutil del contenido detrás del navbar) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <filter id="liquid-glass-distortion" x="-20%" y="-20%" width="140%" height="140%">
        <feTurbulence type="fractalNoise" baseFrequency="0.008 0.012" numOctaves="2" seed="7" result="noise" />
        <feGaussianBlur in="noise" stdDeviation="2.5" result="softNoise" />
        <feDisplacementMap in="SourceGraphic" in2="softNoise" scale="16" xChannelSelector="R" yChannelSelector="G" />
    </filter>
</svg>

<div class="nav-root">
<nav x-data="{ open: false }" @mousemove="
        const r = $el.getBoundingClientRect();
        $el.style.setProperty('--mx', ((event.clientX - r.left) / r.width * 100) + '%');
        $el.style.setProperty('--my', ((event.clientY - r.top) / r.height * 100) + '%');
    ">
    <div class="nav-glass-fx" aria-hidden="true"></div>
    <div class="px-4 sm:px-6 relative z-[1]">
        <div class="flex justify-between h-14">

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

                            <x-dropdown-link :href="route('acciones-correctivas.index')" class="nav-dropdown-item">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#D4A018]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Acciones Correctivas') }}
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

            <!-- DERECHA: Búsqueda + Notificaciones + Perfil -->
            <div class="hidden sm:flex sm:items-center gap-2">
                @if (Auth::check())

                <!-- Búsqueda global -->
                <div class="relative" x-data="{ searchOpen: false, q: '', results: [], loading: false }" @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false">
                    <button type="button" class="nav-icon-btn" aria-label="Buscar"
                        @click="searchOpen = !searchOpen; $nextTick(() => searchOpen && $refs.navSearchInput.focus())">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </button>

                    <div x-show="searchOpen" x-transition.origin.top.right class="nav-search-panel" style="display:none;">
                        <div class="nav-search-input-wrap">
                            <svg class="w-4 h-4 opacity-50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                            <input type="text" x-ref="navSearchInput" x-model="q" placeholder="Buscar solicitudes, documentos…"
                                class="nav-search-input"
                                @input.debounce.350ms="
                                    if (q.trim().length < 2) { results = []; loading = false; return; }
                                    loading = true;
                                    fetch('{{ route('buscar') }}?q=' + encodeURIComponent(q))
                                        .then(r => r.json())
                                        .then(d => { results = d.resultados; loading = false; })
                                        .catch(() => { loading = false; });
                                ">
                        </div>
                        <div class="nav-search-results">
                            <template x-if="loading">
                                <div class="nav-search-empty">Buscando…</div>
                            </template>
                            <template x-if="!loading && q.trim().length >= 2 && results.length === 0">
                                <div class="nav-search-empty">Sin resultados</div>
                            </template>
                            <template x-for="r in results" :key="r.url">
                                <a :href="r.url" class="nav-search-result">
                                    <span class="nav-search-result-tag" x-text="r.tipo"></span>
                                    <span class="nav-search-result-title" x-text="r.titulo"></span>
                                    <span class="nav-search-result-sub" x-text="r.subtitulo"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Notificaciones -->
                @php($__navNotifs = Auth::user()->unreadNotifications()->limit(6)->get())
                <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                    <button type="button" class="nav-icon-btn" aria-label="Notificaciones" @click="notifOpen = !notifOpen">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($__navNotifs->count())
                        <span class="nav-notif-dot"></span>
                        @endif
                    </button>

                    <div x-show="notifOpen" x-transition.origin.top.right class="nav-notif-panel" style="display:none;">
                        <div class="nav-notif-head">
                            <span>Notificaciones</span>
                            @if($__navNotifs->count())
                            <form method="POST" action="{{ route('notificaciones.marcar-todas') }}">
                                @csrf
                                <button type="submit" class="nav-notif-markall">Marcar todas leídas</button>
                            </form>
                            @endif
                        </div>
                        @forelse($__navNotifs as $notif)
                        <a href="{{ route('notificaciones.ir', $notif->id) }}" class="nav-notif-item">
                            <span class="nav-notif-item-dot" aria-hidden="true"></span>
                            <span class="nav-notif-item-body">
                                <span class="nav-notif-item-text">{{ $notif->data['mensaje'] ?? '' }}</span>
                                <span class="nav-notif-item-time">{{ $notif->created_at->diffForHumans() }}</span>
                            </span>
                        </a>
                        @empty
                        <div class="nav-notif-empty">No tienes notificaciones nuevas</div>
                        @endforelse
                    </div>
                </div>

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
        class="overflow-hidden sm:hidden nav-mobile relative z-[1]">
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
            <a href="{{ route('acciones-correctivas.index') }}"
                class="nav-mobile-link {{ request()->routeIs('acciones-correctivas.*') ? 'nav-mobile-link-active' : '' }}">
                {{ __('Acciones Correctivas') }}
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
