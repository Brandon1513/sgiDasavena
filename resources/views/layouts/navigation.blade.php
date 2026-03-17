<nav x-data="{ open: false }" class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200 shadow-sm">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- IZQUIERDA: Logo y Menús -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}" class="transition-transform duration-200 hover:scale-105">
                        <x-application-logo class="block w-auto text-slate-800 fill-current h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                @if (Auth::check())
                <div class="hidden space-x-2 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-200' }}">
                        {{ __('Inicio') }}
                    </x-nav-link>

                    <!-- Menú Administración -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            @role('administrador')
                            <button class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <div>Administración</div>
                                <div class="ms-2">
                                    <svg class="w-4 h-4 fill-current transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                            @endrole
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('usuarios.index')" class="hover:bg-blue-50 transition-colors">
                                {{ __('Usuarios') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Menú SGI -->
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <div>SGI</div>
                                <div class="ms-2">
                                    <svg class="w-4 h-4 fill-current transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('solicitudes.index')" class="hover:bg-blue-50 transition-colors">
                                {{ __('Solicitudes') }}
                            </x-dropdown-link>

                            @role('administrador_sgi')
                            <x-dropdown-link :href="route('solicitudes.calendar')" class="hover:bg-blue-50 transition-colors">
                                {{ __('Calendario') }}
                            </x-dropdown-link>
                            @endrole
                        </x-slot>
                    </x-dropdown>
                </div>
                @endif
            </div>

            <!-- DERECHA: Menú de Perfil -->
            <div class="hidden space-x-4 sm:flex sm:items-center">
                @if (Auth::check())
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-bold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div>{{ Auth::user()->name }}</div>
                            </div>
                            <div class="ms-2">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="hover:bg-blue-50 transition-colors">
                            {{ __('Perfil') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" 
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="hover:bg-red-50 transition-colors text-red-600">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-all duration-200 hover:shadow-md">Iniciar Sesión</a>
                @endif
            </div>

            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-slate-600 rounded-lg transition-all duration-150 hover:bg-slate-200 focus:outline-none focus:bg-slate-200">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                class="px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-500 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @role('administrador')
            <x-responsive-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.index')"
                class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                {{ __('Usuarios') }}
            </x-responsive-nav-link>
            @endrole
            <x-responsive-nav-link :href="route('solicitudes.index')" :active="request()->routeIs('solicitudes.index')"
                class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                {{ __('Solicitudes') }}
            </x-responsive-nav-link>
            @role('administrador_sgi')
            <x-responsive-nav-link :href="route('solicitudes.calendar')" :active="request()->routeIs('solicitudes.calendar')"
            class="px-3 py-2 rounded-lg text-slaye-700 hover:bg-slate-100">
            {{ __('calendario') }}
            </x-responsive-nav-link>
            @endrole
        </div>

        @if (Auth::check())
        <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="px-4 py-3">
                <div class="text-base font-medium text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" 
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @else
        <div class="pt-4 pb-1 border-t border-slate-200 px-4">
            <a href="{{ route('login') }}" class="block px-3 py-2 text-center text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors">
                {{ __('Iniciar Sesión') }}
            </a>
        </div>
        @endif
    </div>
</nav>
