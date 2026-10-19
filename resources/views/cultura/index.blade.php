{{--
    Cultura Dasavena: animación 3D de la caja que se arma con el scroll y presenta los valores.
    La ruta es pública: con sesión se muestra con la navegación de la app; sin sesión, con el
    layout de invitado (la navegación usa datos del usuario y no puede renderizarse sin él).
--}}
<x-dynamic-component :component="auth()->check() ? 'app-layout' : 'guest-layout'">
    @include('cultura.partials.caja-3d', ['valores' => $valores])
</x-dynamic-component>
