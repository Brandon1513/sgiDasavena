<?php

return [
    'site_host' => env('SP_SITE_HOST'),
    'site_path' => env('SP_SITE_PATH'),
    'drive_id'  => env('SP_DRIVE_ID'),
    'drive_name'=> env('SP_DRIVE_NAME'),

    // Carpeta base donde están las carpetas
    // Ej: "Sistema de Gestión de Inocuidad/SGI"
    'root_folder' => env('SP_ROOT_FOLDER', ''),

    // Nombres EXACTOS de las carpetas en SharePoint:
    'folder_vigentes'  => env('SP_FOLDER_VIGENTES', 'Sistema de Gestión Vigente'),
    'folder_obsoletos' => env('SP_FOLDER_OBSOLETOS', 'Sistema de Gestión Obsoleto'),
];
