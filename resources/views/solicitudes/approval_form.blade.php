<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold tracking-tight text-[#4a2a55] font-['Century_Gothic',sans-serif]">
            {{ __('Revisión de Solicitud') }} 
            <span class="text-[#D4A018]">#{{ $solicitud->id }}</span>
        </h2>
    </x-slot>

    <div class="py-12 font-['Century_Gothic',sans-serif]">
        <div class="max-w-2xl mx-auto">
            <div class="overflow-hidden bg-white border border-[#6A2C75]/10 shadow-[0_10px_30px_rgba(106,44,117,0.08)] rounded-xl">
                
                <div class="px-6 py-4 border-b border-[#6A2C75]/10 bg-[#FAF7FB]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold tracking-widest text-[#6A2C75]/60 uppercase">Detalles de la Acción</p>
                            <h3 class="text-lg font-bold text-[#4a2a55]">{{ ucfirst($solicitud->accion) }}</h3>
                        </div>
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-[#D4A018]/10 text-[#D4A018] border border-[#D4A018]/20">
                            Pendiente de Revisión
                        </span>
                    </div>
                </div>

                <div class="p-8">
                    <form method="POST" action="{{ route('solicitudes.approve_or_reject', $solicitud->id) }}">
                        @csrf

                        <div class="space-y-2">
                            <label for="observaciones_jefe" class="flex items-center gap-2 text-sm font-bold text-[#4a2a55]">
                                <svg class="w-4 h-4 text-[#6A2C75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Observaciones del Revisor (Opcional)
                            </label>
                            <textarea 
                                id="observaciones_jefe"
                                name="observaciones_jefe" 
                                rows="4"
                                placeholder="Escribe aquí los motivos de la decisión..."
                                class="w-full px-4 py-3 rounded-lg border-[#6A2C75]/20 text-[#4a2a55] focus:border-[#D4A018] focus:ring focus:ring-[#D4A018]/20 transition-all duration-200 placeholder:text-[#6A2C75]/30"
                            ></textarea>
                        </div>

                        <div class="flex flex-col gap-4 mt-8 sm:flex-row sm:justify-between">
                            <button type="submit" name="decision" value="rechazado_jefe"
                                class="inline-flex items-center justify-center order-2 px-6 py-3 text-sm font-bold tracking-wide text-red-700 transition-all duration-200 bg-red-50 border-2 border-red-100 rounded-lg sm:order-1 hover:bg-red-100 hover:border-red-200 active:scale-95">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Denegar Solicitud
                            </button>

                            <button type="submit" name="decision" value="aprobado_jefe"
                                class="inline-flex items-center justify-center order-1 px-8 py-3 text-sm font-bold tracking-widest text-white transition-all duration-250 bg-gradient-to-br from-[#6A2C75] to-[#8e3d9e] rounded-lg shadow-[0_5px_15px_rgba(106,44,117,0.3)] sm:order-2 hover:shadow-[0_8px_20px_rgba(106,44,117,0.4)] hover:-translate-y-0.5 active:scale-95">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                APROBAR SOLICITUD
                            </button>
                        </div>
                    </form>
                </div>

                <div class="h-1 bg-gradient-to-r from-[#6A2C75] via-[#D4A018] to-[#6A2C75] opacity-50"></div>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('solicitudes.index') }}" class="text-sm font-semibold text-[#6A2C75]/60 hover:text-[#6A2C75] transition-colors">
                    &larr; Volver al listado sin cambios
                </a>
            </div>
        </div>
    </div>
</x-app-layout>