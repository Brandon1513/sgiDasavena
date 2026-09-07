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

];
