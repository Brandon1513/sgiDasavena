<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## SGI Dasavena — Publicación de documentos en SharePoint

El archivo oficial de cada versión de documento se publica en una biblioteca de
SharePoint (vía Microsoft Graph), reusando la misma app de Entra ID que ya se
usa para enviar correo (`MS_TENANT_ID`/`MS_CLIENT_ID`/`MS_CLIENT_SECRET`).

### Permiso de Graph requerido: `Sites.Selected`, no `Sites.ReadWrite.All`

La app debe tener el permiso de aplicación **`Sites.Selected`** otorgado en
Entra ID (consentimiento de administrador), y además el acceso de **escritura
a este sitio en particular** se concede aparte, directamente contra el sitio
— no se usa `Sites.ReadWrite.All` (que daría acceso de escritura a *todos*
los sitios del tenant).

Para otorgar el acceso de escritura solo a este sitio, con un token de Graph
que tenga permiso `Sites.FullControl.All` o seas administrador del sitio,
hay que hacer un `POST` a:

```
POST https://graph.microsoft.com/v1.0/sites/{site-id}/permissions
Content-Type: application/json

{
  "roles": ["write"],
  "grantedToIdentities": [
    {
      "application": {
        "id": "{MS_CLIENT_ID}",
        "displayName": "sgiDasavena"
      }
    }
  ]
}
```

Donde `{site-id}` es el valor configurado en `SP_SITE_ID` (formato
`{host},{siteCollectionId},{webId}`). Esto se hace una sola vez por sitio;
después de concedido, la app puede leer/escribir ese sitio con sus propias
credenciales de `client_credentials`, sin tocar ningún otro sitio del tenant.

### Variables de entorno

Ver `.env.example` (sección SharePoint): `SP_SITE_HOST`, `SP_SITE_PATH`,
`SP_SITE_ID`, `SP_DRIVE_NAME`, `SP_DRIVE_ID`, `SP_ROOT_FOLDER`, `SP_CACHE_TTL`.

### Migrar documentos ya existentes

```
php artisan sharepoint:publicar-existentes --dry-run
php artisan sharepoint:publicar-existentes
php artisan sharepoint:publicar-existentes --documento=123
```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
