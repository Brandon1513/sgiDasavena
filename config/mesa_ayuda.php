<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integración con Mesa de Ayuda
    |--------------------------------------------------------------------------
    |
    | URL base y token de servicio (emitido con
    | `php artisan integrations:sgidasavena:token` en el proyecto mesa-ayuda)
    | usados para reenviar los tickets del chatbot de soporte como tickets
    | reales de Mesa de Ayuda.
    |
    */

    'base_url' => env('MESA_AYUDA_BASE_URL'),

    'token' => env('MESA_AYUDA_API_TOKEN'),

];
