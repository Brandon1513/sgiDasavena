<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Revisar Solicitud #{{ $solicitud->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl p-6 mx-auto bg-white rounded shadow">
            <p><strong>Acción Solicitada:</strong> {{ ucfirst($solicitud->accion) }}</p>

            <form method="POST" action="{{ route('solicitudes.approve_or_reject', $solicitud->id) }}">
                @csrf

                <div class="mt-4">
                    <label class="block text-gray-700">Observaciones (opcional)</label>
                    <textarea name="observaciones_jefe" class="w-full p-2 border rounded"></textarea>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="submit" name="decision" value="rechazado_jefe"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                        Rechazar
                    </button>
                    <button type="submit" name="decision" value="aprobado_jefe"
                        class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Aprobar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
