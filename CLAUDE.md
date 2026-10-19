# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Sistema de Gestión Integral (SGI) de Dasavena: control documental (solicitudes de alta/actualización/baja de documentos, versiones, revisiones, vencimientos), publicación en SharePoint, acciones correctivas e integración con Mesa de Ayuda. Laravel 12 + Blade/Alpine/Tailwind 3, Pest. Todo el código, comentarios, mensajes y textos de UI están en español: mantenerlo así.

## Entorno y comandos

La app corre con **Laravel Sail en Docker** (contenedores `sgidasavena-laravel.test-1`, `sgidasavena-queue-1`, `mysql`, `redis`, `meilisearch`, `mailpit`, `selenium`). La base de datos usa el host `mysql`, que **no se resuelve desde Windows**: todo lo que toque la BD (tests, migraciones, tinker con modelos persistidos) se corre dentro del contenedor.

```bash
# Tests (Pest; usan la BD "testing" del contenedor mysql)
docker exec sgidasavena-laravel.test-1 php artisan test
docker exec sgidasavena-laravel.test-1 php artisan test tests/Feature/SharePoint/ObsoletosManualesTest.php
docker exec sgidasavena-laravel.test-1 php artisan test --filter="nombre del test"

# Frontend (Vite; escribe public/hot y el puerto puede variar: 5173/5174)
npm run dev
npm run build

# Formato PHP
vendor/bin/pint

# Tinker desde el host sin BD (sesión y caché en memoria; sirve para Graph/SharePoint y render de vistas)
SESSION_DRIVER=array CACHE_STORE=array php artisan tinker --execute "..."
```

- El worker de la cola (`sgidasavena-queue-1`, `queue:work`) carga el código al arrancar: **después de cambiar un Job o lo que este llama, correr `php artisan queue:restart`** (el contenedor tiene `restart: unless-stopped` y se vuelve a levantar solo).
- Comandos propios: `sharepoint:diagnostico` (valida Graph sin modificar nada), `sharepoint:prueba-subida {--carpeta=}`, `sharepoint:publicar-existentes {--dry-run} {--documento=}`, `documentos:verificar-vencimientos` y `acciones-correctivas:verificar-vencimientos` (programados diario 08:00 en `routes/console.php`), `mail:test`.

## Arquitectura

### Roles y acceso
Spatie Permission con roles `administrador`, `usuario`, `jefe`, `gerencia`, `administrador_sgi` (`RolesSeeder`). Los grupos de rutas en `routes/web.php` se separan por middleware `role:`. Los documentos se filtran por `area` del usuario (salvo `administrador_sgi`).

### Flujo de solicitudes → documentos (`SolicitudFormatoController`)
- `SolicitudFormato.accion`: `nuevo_documento`, `actualizacion`, `baja`.
- Estados: `pendiente` → `aprobado_jefe` (lo aprueba el jefe; si la crea `gerencia` queda aprobada automáticamente) → `atendido` / `rechazado_sgi` (lo decide `administrador_sgi` en `finalize`).
- `finalize()` corre en una transacción: crea la `DocumentoVersion` nueva, marca como `obsoleto`/`obsoleta` las versiones y revisiones anteriores y despacha los jobs de SharePoint con `->afterCommit()`. La baja solo decomisiona (no captura datos oficiales).
- Modelo: `Documento` → `DocumentoVersion` (`version_vigente_id`) → `DocumentoRevision`. Estatus de versión `vigente`/`obsoleto`, de revisión `vigente`/`obsoleta`, de documento `vigente`/`baja`.

### SharePoint (Microsoft Graph)
- **Convención: Graph se llama con el facade `Http` (Guzzle) directo, nunca con el SDK `microsoft/microsoft-graph`.** Token por client credentials en `App\Services\Graph\GraphAccessTokenProvider` (cacheado en `graph_api_access_token`; config en `config/graph.php`).
- `App\Services\SharePointService`: operaciones de drive (listar con paginación `@odata.nextLink`, crear carpetas, subir simple o por upload session >4 MB, mover con `conflictBehavior=rename`), reintentos en 429/5xx, y `fallar()` registra el detalle y lanza un mensaje genérico.
- `App\Actions\SharePoint\PublishDocumentoVersion`: sugiere la carpeta vigente `{root_folder}/{Área}/{Tipo}`, publica y mueve a Obsoletos `{obsoleto_root_folder}/{Área}/{Tipo} obsoletos`. `ClasificadorDocumentoSharePoint` normaliza tipos/áreas contra los nombres reales de carpetas. Regla de diseño: si el Área o el tipo no se identifican con confianza, **no se adivina** (devuelve null / no mueve y lo registra); nunca crea carpetas de Área nuevas.
- En el tenant real, `obsoleto_root_folder` vive **dentro** de `root_folder` (`…/SGI/Sistema de Gestión Obsoleto`); el filtro `estaEnVigentes` lo tiene en cuenta.
- Versiones anteriores al sistema (importadas con `ImportDocumentosSeeder`/`DocumentosImport`) no tienen `sp_item_id`: el administrador marca sus archivos en el selector de `finalize_form` (`sp_obsoletos_items[]`) y se mueven a Obsoletos **antes** de subir el archivo nuevo (para que una subida con el mismo nombre no los sobrescriba).
- Jobs: `PublicarVersionEnSharePoint`, `MoverVersionASharePointObsoletos`, `MoverArchivosSharePointAObsoletos`. **No usar `readonly` en sus propiedades**, y toda propiedad nueva de un job ya en producción va declarada aparte con valor por defecto (no como parámetro promovido): los jobs ya encolados se deserializan sin ella y truenan con "must not be accessed before initialization".
- Selector de carpetas (`SharePointCarpetasController`, solo `administrador_sgi`) y Explorador de solo lectura (`SharePointExploradorController`, cualquier usuario).
- En tests: `Cache::put('graph_api_access_token', ...)` + `Http::fake(...)` simulando el árbol de carpetas (ver `tests/Feature/SharePoint`).

### Otros módulos
- **Correo**: transportes propios `graph` y `graph_soporte` (`App\Mail\Transport\GraphApiTransport`, registrados en `AppServiceProvider`).
- **Acciones correctivas** (`app/Domains/Incidencias`): máquina de estados `EstadoAccionCorrectiva` (borrador → abierta → contención → análisis → validación de causa → plan de acción → ejecución → verificación de cierre → espera de eficacia → verificación de eficacia → cerrada), una Action por paso y `CambiarEstadoAccionCorrectiva` como única vía de transición. Folios con `GenerarFolioAccionCorrectiva` (insertOrIgnore + lockForUpdate; la transacción la controla quien crea). Modelos con `spatie/laravel-activitylog`.
- **Mesa de Ayuda**: `App\Services\MesaAyuda\MesaAyudaApiClient` reenvía tickets del chatbot de soporte (`MESA_AYUDA_*`).
- **Login con Microsoft**: Socialite + `socialiteproviders/microsoft` (`MicrosoftLoginController`).

### Frontend
- Blade + Alpine 3 (`resources/js/app.js`) + Tailwind 3 con colores `dasavena-purple`/`dasavena-gold` y fuente `font-display` (Century Gothic) en `tailwind.config.js`. Iconos Tabler cargados por CDN en cada vista que los usa.
- Estilo de referencia: el dashboard (`components/dashboard/*`: `x-dashboard.panel`, `kpi-widget`, `sla-row`): fondo con patrón y luces difusas, hero morado degradado, paneles translúcidos `rounded-[28px]`.
- Trampas conocidas de Alpine en este proyecto:
  - `x-for` necesita un solo elemento raíz dentro del `<template>`.
  - No usar `@json()` dentro de un atributo `x-data`; usar `{{ json_encode(...) }}` (Blade escapa las comillas).
  - Los paneles usan `backdrop-filter`, que atrapa a los hijos `position: fixed`: los modales dentro de paneles van con `<template x-teleport="body">`.
- `/cultura` es **pública**: usa `<x-dynamic-component>` con `guest-layout` cuando no hay sesión, porque `layouts/navigation.blade.php` usa `Auth::user()`. La caja 3D vive en `resources/js/cultura-caja/` (Three.js + Anime.js v4, entrada Vite propia); texturas, medidas, tiempos y recorrido de scroll se ajustan en `config.js`. El arte del empaque está en `public/images/dasavena/`.
