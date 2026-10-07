<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sitio de SharePoint
    |--------------------------------------------------------------------------
    |
    | site_id ya resuelto (formato "{host},{siteCollectionId},{webId}") evita
    | tener que llamar a /sites/{host}:{path} en cada publicación. Si no está
    | configurado, el servicio cae a resolverlo por host+path (y lo cachea).
    |
    */

    'site_id' => env('SP_SITE_ID'),
    'site_host' => env('SP_SITE_HOST'),
    'site_path' => env('SP_SITE_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Biblioteca documental (drive)
    |--------------------------------------------------------------------------
    |
    | drive_id es el valor fuerte, se resuelve una sola vez por nombre
    | (drive_name) y se cachea; cuando se obtenga por Microsoft Graph,
    | conviene fijarlo aquí para no depender de la búsqueda por nombre.
    |
    */

    'drive_id' => env('SP_DRIVE_ID'),
    'drive_name' => env('SP_DRIVE_NAME', 'Documentos'),

    /*
    |--------------------------------------------------------------------------
    | Carpeta raíz
    |--------------------------------------------------------------------------
    |
    | Ruta dentro de la biblioteca donde se crean las carpetas
    | "{root}/{Área}/Vigentes/{Código}" y "{root}/{Área}/Obsoletos/{Código}".
    | Vacío = raíz de la biblioteca.
    |
    */

    'root_folder' => env('SP_ROOT_FOLDER', ''),

    /*
    |--------------------------------------------------------------------------
    | Tiempo de vida de la caché de IDs (sitio, drive, carpetas)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => env('SP_CACHE_TTL', 3600),
];
