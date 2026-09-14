<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Microsoft Entra ID / Graph credentials
    |--------------------------------------------------------------------------
    |
    | Credenciales de la aplicación registrada en Microsoft Entra ID usada
    | para autenticarse contra Microsoft Graph mediante el flujo de
    | "client credentials" (permisos de tipo Application).
    |
    */

    'tenant_id' => env('MS_TENANT_ID'),
    'client_id' => env('MS_CLIENT_ID'),
    'client_secret' => env('MS_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Mailbox usado para enviar correo
    |--------------------------------------------------------------------------
    |
    | Buzón (userPrincipalName o id) sobre el cual se hace
    | POST /users/{mail_from}/sendMail. La app debe tener el permiso
    | Mail.Send de tipo Application con consentimiento de administrador.
    |
    */

    'mail_from' => env('MS_MAIL_FROM'),

    /*
    |--------------------------------------------------------------------------
    | Buzón de soporte IT
    |--------------------------------------------------------------------------
    |
    | Buzón desde el cual se envían los tickets/ideas generados por el
    | asistente de soporte del dashboard, y correo destino del área de
    | sistemas que los recibe. Si el buzón de soporte aún no existe, cae
    | por defecto al mismo buzón usado para el resto de notificaciones.
    |
    */

    'mail_from_soporte' => env('MS_MAIL_FROM_SOPORTE', env('MS_MAIL_FROM')),

    'mail_to_soporte' => env('MS_MAIL_TO_SOPORTE', 'aux.sistemas@dasavena.com'),

];
