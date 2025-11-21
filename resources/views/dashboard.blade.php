<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}

        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex flex-col items-center bg-gray-50 rounded-t-lg">
                    <!-- bienvenida a Dasavena AI -->
                    <h1 class="text-2xl font-bold mb-4">Bienvenido a DasavenaSGI</h1>
                    <p class="mb-4 fot-blod ">Esta es tu área de trabajo donde puedes interactuar con nuestra inteligencia artificial para resolver tus dudas y obtener información.</p>

                </div>

                <div class="p-4 pb-100 bg-white border-b border-gray-200 flex flex-col items-center  pb-20">
                    <!-- Aquí puedes agregar más contenido o componentes según sea necesario -->
                    <!-- Barra de búsqueda -->
                    <x-BarraBusqueda  />

                    
                </div>
</x-app-layout>