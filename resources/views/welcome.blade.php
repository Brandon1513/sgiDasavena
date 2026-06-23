<x-app-layout>
<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    .welcome-root {
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        min-height: 100vh;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    /* ── Imagen de fondo ── */
    .welcome-bg {
        position: absolute;
        inset: 0;
        background-image: url('{{ asset("images/background-pattern.png") }}');
        background-size: cover;
        background-position: center;
        opacity: 0.06;
    }

    /* ── Orbes decorativos claros ── */
    .orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        pointer-events: none;
    }
    .orb-purple {
        width: 650px; height: 650px;
        background: radial-gradient(circle, rgba(106,44,117,0.18) 0%, transparent 70%);
        top: -180px; left: -180px;
        animation: drift 14s ease-in-out infinite alternate;
    }
    .orb-gold {
        width: 480px; height: 480px;
        background: radial-gradient(circle, rgba(212,160,24,0.15) 0%, transparent 70%);
        bottom: -120px; right: -100px;
        animation: drift 17s ease-in-out infinite alternate-reverse;
    }
    .orb-mid {
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(180,100,200,0.10) 0%, transparent 70%);
        top: 40%; left: 40%;
        animation: drift 20s ease-in-out infinite alternate;
    }

    @keyframes drift {
        from { transform: translate(0, 0) scale(1); }
        to   { transform: translate(30px, 20px) scale(1.06); }
    }

    /* ── Líneas geométricas SVG ── */
    .geo-lines {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }
    .geo-lines svg { width: 100%; height: 100%; opacity: 0.07; }

    /* ── Líneas verticales laterales ── */
    .vertical-line {
        position: absolute;
        left: 5%; top: 10%; height: 80%; width: 1px;
        background: linear-gradient(to bottom, transparent, rgba(106,44,117,0.3), transparent);
        z-index: 2;
    }
    .vertical-line-right {
        left: auto; right: 5%;
        background: linear-gradient(to bottom, transparent, rgba(212,160,24,0.25), transparent);
    }

    /* ── Tarjeta central ── */
    .welcome-card {
        position: relative;
        z-index: 10;
        max-width: 780px;
        width: 90%;
        text-align: center;
        animation: fadeUp 1s ease both;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Badge ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(212, 160, 24, 0.1);
        border: 1px solid rgba(212, 160, 24, 0.4);
        color: #b38600;
        font-size: 0.72rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        padding: 6px 18px;
        border-radius: 100px;
        margin-bottom: 28px;
        animation: fadeUp 1s ease 0.1s both;
    }
    .badge-dot {
        width: 6px; height: 6px;
        background: #D4A018;
        border-radius: 50%;
        animation: pulse 2s ease infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.4; transform: scale(1.5); }
    }

    /* ── Título ── */
    .welcome-title {
        font-size: clamp(2.4rem, 6vw, 4.2rem);
        font-weight: 700;
        color: #2d1033;
        line-height: 1.1;
        letter-spacing: -0.02em;
        margin-bottom: 10px;
        animation: fadeUp 1s ease 0.2s both;
    }
    .welcome-title .highlight {
        color: transparent;
        background: linear-gradient(100deg, #6A2C75 0%, #a044b8 50%, #D4A018 100%);
        -webkit-background-clip: text;
        background-clip: text;
    }

    /* ── Divisor ── */
    .divider {
        width: 60px; height: 2px;
        background: linear-gradient(90deg, transparent, #D4A018, transparent);
        margin: 18px auto 22px;
        animation: fadeUp 1s ease 0.3s both;
    }

    /* ── Subtítulo ── */
    .welcome-subtitle {
        font-size: clamp(0.97rem, 2.2vw, 1.15rem);
        color: #5a4a65;
        max-width: 540px;
        margin: 0 auto 36px;
        line-height: 1.75;
        animation: fadeUp 1s ease 0.35s both;
    }

    /* ── Botón CTA ── */
    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif;
        font-size: 0.88rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #6A2C75 0%, #8e3d9e 100%);
        padding: 15px 42px;
        border-radius: 6px;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 6px 28px rgba(106, 44, 117, 0.28);
        animation: fadeUp 1s ease 0.5s both;
    }
    .btn-cta::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #D4A018 0%, #f0c84a 100%);
        opacity: 0;
        transition: opacity 0.35s ease;
    }
    .btn-cta:hover { transform: translateY(-3px); box-shadow: 0 12px 36px rgba(106, 44, 117, 0.32); }
    .btn-cta:hover::before { opacity: 1; }
    .btn-cta:hover span, .btn-cta:hover svg { color: #2d1033; }
    .btn-cta span, .btn-cta svg { position: relative; z-index: 1; transition: color 0.3s; }

    /* ── Chips inferiores ── */
    .features {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 46px;
        animation: fadeUp 1s ease 0.65s both;
    }
    .feature-chip {
        display: flex;
        align-items: center;
        gap: 7px;
        background: rgba(106,44,117,0.06);
        border: 1px solid rgba(106,44,117,0.15);
        border-radius: 100px;
        padding: 7px 16px;
        font-size: 0.75rem;
        color: #6A2C75;
        letter-spacing: 0.04em;
    }
    .feature-chip svg { color: #D4A018; flex-shrink: 0; }

    /* ── Logo esquina ── */

    /*logo responsivo */     
        .corner-logo {
            position:absolute;
            top: 7%;
            left: 50%;
            transform: translateX(-50%);
            width: 130px;
            opacity: 0.7;
            transition: opacity 0.3s;
            z-index: 20;
          
        }
        

        @media (max-width: 768px) {
        .corner-logo {
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
        }
        }

        @media (max-width: 480px) {
        .corner-logo {
            top: 5%;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
        }
        }
    .corner-logo:hover { opacity: 1; }
</style>

<div class="welcome-root">
    <div class="welcome-bg"></div>

    <div class="orb orb-purple"></div>
    <div class="orb orb-gold"></div>
    <div class="orb orb-mid"></div>

    <div class="vertical-line"></div>
    <div class="vertical-line vertical-line-right"></div>

    <div class="geo-lines">
        <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <line x1="0" y1="900" x2="600" y2="0" stroke="#D4A018" stroke-width="1"/>
            <line x1="1440" y1="0" x2="900" y2="900" stroke="#6A2C75" stroke-width="1"/>
            <circle cx="720" cy="450" r="380" fill="none" stroke="#6A2C75" stroke-width="0.8"/>
            <circle cx="720" cy="450" r="260" fill="none" stroke="#D4A018" stroke-width="0.5"/>
        </svg>
    </div>

    <div class="welcome-card">
        <div class="badge">
            <span class="badge-dot"></span>
            Sistema de Gestión Integral
        </div>

        <h1 class="welcome-title">
            Bienvenido a<br>
            <span class="highlight">Dasavena SGI</span>
        </h1>

        <div class="divider"></div>

        <p class="welcome-subtitle">
            Encuentra información de apoyo, involúcrate en los procesos administrativos y sé parte activa de Dasavena.
        </p>

        <a href="{{ route('login') }}" class="btn-cta">
            <span>Iniciar Sesión</span>
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>

        <div class="features">
            <div class="feature-chip">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Gestión Administrativa
            </div>
            <div class="feature-chip">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Acceso Seguro
            </div>
            <div class="feature-chip">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Información Centralizada
            </div>
        </div>
    </div>

    <img src="{{ asset('images/logo.png') }}" alt="Dasavena Logo" class="corner-logo">
</div>
</x-app-layout>