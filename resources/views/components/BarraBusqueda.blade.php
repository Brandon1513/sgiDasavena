<div class="flex items-center justify-center w-full">
    <form action="" method="GET" class="w-full max-w-md">
        <div class="relative">
            <input
                type="text"
                name="q"
                placeholder="Buscar..."
                class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm transition">
            <span class="absolute left-3 top-2.5 text-gray-400">

                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </span>
        </div>
    </form>


</div>

@if(request('q'))
<div class="mt-4 w-full max-w-md mx-auto bg-white rounded-lg shadow p-4 hover:shadow-lg transition divide-y divide-gray-200">

    <h2 class="text-gray-700 text-sm mb-2">Resultados para: <span class="font-semibold">{{ request('q') }}</span></h2>

    <ul class="divide-y divide-gray-200">
        <!-- Resultados dinamicos  -->
        <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">{{ __('Profile') }}</li>
        <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 1</li>
        <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 2</li>
        <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 3</li>
    </ul>
        <div class ="py-2 mt-4 text-center text-gray-500 text-sm divide-y divide-gray-200"> 
            <a href="{{ route('solicitudes.index') }}" class="text-blue-600 hover:underline   ">Ver todas las solicitudes</a>

            
    
        
    </div>

</div>
@endif