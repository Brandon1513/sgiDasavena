<x-guest-layout>
    <div class="relative min-h-screen w-full flex items-center justify-center bg-[#faf7fb] overflow-hidden font-['Century_Gothic',sans-serif] p-4 md:p-8">
        
        <div class="absolute inset-0 opacity-[0.06] pointer-events-none" 
             style="background-image: url('{{ asset('images/background-pattern.png') }}'); background-size: cover; background-position: center;">
        </div>

        <div class="absolute -top-[10%] -left-[10%] w-[40vw] h-[40vw] max-w-[650px] max-h-[650px] rounded-full opacity-60 pointer-events-none blur-[80px] md:blur-[120px] bg-[radial-gradient(circle,rgba(106,44,117,0.18)_0%,transparent_70%)] animate-[drift_14s_ease-in-out_infinite_alternate]"></div>
        
        <div class="absolute -bottom-[10%] -right-[5%] w-[35vw] h-[35vw] max-w-[500px] max-h-[500px] rounded-full opacity-60 pointer-events-none blur-[80px] md:blur-[120px] bg-[radial-gradient(circle,rgba(212,160,24,0.15)_0%,transparent_70%)] animate-[drift_17s_ease-in-out_infinite_alternate-reverse]"></div>

        <div class="absolute inset-0 pointer-events-none opacity-[0.07]">
            <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <line x1="0" y1="900" x2="600" y2="0" stroke="#D4A018" stroke-width="1"/>
                <line x1="1440" y1="0" x2="900" y2="900" stroke="#6A2C75" stroke-width="1"/>
                <circle cx="720" cy="450" r="380" fill="none" stroke="#6A2C75" stroke-width="0.8"/>
            </svg>
        </div>

        <div class="absolute left-[5%] top-[15%] h-[70%] w-px bg-gradient-to-b from-transparent via-[#6A2C75]/30 to-transparent hidden md:block"></div>
        <div class="absolute right-[5%] top-[15%] h-[70%] w-px bg-gradient-to-b from-transparent via-[#D4A018]/25 to-transparent hidden md:block"></div>

        <div class="relative z-10 w-full max-w-[450px] bg-white/70 backdrop-blur-2xl border border-white/50 rounded-3xl shadow-[0_25px_50px_-12px_rgba(106,44,117,0.15)] p-8 md:p-12 animate-[fadeUp_0.8s_ease-out_both]">
            
            <div class="text-center mb-8">
                <div class="inline-block p-3 rounded-2xl bg-white/50 mb-4 shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-max md:h-16 w-auto object-contain">
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-[#2d1033] tracking-tight">Acceso SGI</h2>
                <p class="text-sm text-[#5a4a65] mt-2 opacity-80">Ingresa tus credenciales para continuar</p>
            </div>

            <x-auth-session-status class="mb-4 text-center font-medium" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="group">
                    <label for="email" class="block text-[0.7rem] uppercase tracking-[0.15em] font-black text-[#6A2C75] mb-2 ml-1 transition-colors group-focus-within:text-[#D4A018]">
                        Correo Institucional
                    </label>
                    <input id="email" type="email" name="email" :value="old('email')" required autofocus 
                           class="w-full px-5 py-3.5 bg-white/80 border border-purple-900/10 rounded-xl focus:ring-4 focus:ring-[#D4A018]/10 focus:border-[#6A2C75] transition-all duration-300 outline-none text-[#2d1033] shadow-sm"
                           placeholder="usuario@dasavena.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
                </div>

                <div class="group">
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label for="password" class="text-[0.7rem] uppercase tracking-[0.15em] font-black text-[#6A2C75] transition-colors group-focus-within:text-[#D4A018]">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[0.65rem] font-bold text-[#D4A018] hover:text-[#6A2C75] transition-colors uppercase tracking-wider">
                                ¿La olvidaste?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required 
                           class="w-full px-5 py-3.5 bg-white/80 border border-purple-900/10 rounded-xl focus:ring-4 focus:ring-[#D4A018]/10 focus:border-[#6A2C75] transition-all duration-300 outline-none text-[#2d1033] shadow-sm">
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-[#6A2C75] focus:ring-[#6A2C75]/20 transition-all">
                        <span class="ms-2 text-xs font-bold text-[#5a4a65] group-hover:text-[#6A2C75] transition-colors">Recordarme</span>
                    </label>
                </div>

                <button type="submit" 
                        class="group relative w-full overflow-hidden bg-[#6A2C75] py-4 rounded-xl font-bold text-white text-xs uppercase tracking-[0.25em] shadow-xl shadow-purple-900/20 transition-all hover:-translate-y-1 active:scale-[0.95]">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#D4A018] to-[#f0c84a] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <span class="relative z-10 flex items-center justify-center gap-2 group-hover:text-[#2d1033] transition-colors duration-300">
                        Iniciar Sesión
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </button>
            </form>
        </div>

        <div class="absolute bottom-10 right-10 hidden xl:block opacity-30 hover:opacity-100 transition-opacity duration-500">
            <img src="{{ asset('images/dasavena-logo.png') }}" alt="Logo Footer" class="w-28 filter grayscale">
        </div>
    </div>

    <style>
        @keyframes drift {
            from { transform: translate(0, 0) rotate(0deg); }
            to   { transform: translate(40px, 30px) rotate(5deg); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        /* Corregir Century Gothic si no está en el sistema */
        @font-face {
            font-family: 'Century Gothic';
            src: local('Century Gothic'), local('AppleGothic'), sans-serif;
        }
    </style>
</x-guest-layout>