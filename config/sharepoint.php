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
    | Carpeta raíz de VIGENTES
    |--------------------------------------------------------------------------
    |
    | Ruta real donde cada Área tiene su propia carpeta, y dentro de ella una
    | por Tipo de documento: "{root}/{Área}/{Tipo}/{Código}". Vacío = raíz de
    | la biblioteca.
    |
    */

    'root_folder' => env('SP_ROOT_FOLDER', ''),

    /*
    |--------------------------------------------------------------------------
    | Carpeta raíz de OBSOLETOS
    |--------------------------------------------------------------------------
    |
    | Ruta real y fija de "Sistema de Gestión Obsoleto" — vive en una rama
    | aparte de la de vigentes (no se mueve si cambia root_folder). Dentro:
    | "{obsoleto_root_folder}/{Área}/{Tipo} obsoletos".
    |
    */

    'obsoleto_root_folder' => env('SP_OBSOLETO_ROOT_FOLDER', ''),

    /*
    |--------------------------------------------------------------------------
    | Tiempo de vida de la caché de IDs (sitio, drive, carpetas)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => env('SP_CACHE_TTL', 3600),
];
