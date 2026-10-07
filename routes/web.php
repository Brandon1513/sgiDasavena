<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SgiDashboardController;
use App\Http\Controllers\SolicitudFormatoController;
use App\Http\Controllers\SolicitudesCalendarController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DocumentoVersionController;
use App\Http\Controllers\DocumentoRevisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoUsuarioController;
use App\Http\Controllers\AccionCorrectivaController;
use App\Http\Controllers\SoporteTicketController;
use App\Http\Controllers\CulturaController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\NotificacionController;
use App\Domains\Incidencias\Actions\ProponerCausaRaiz;
use App\Http\Controllers\Auth\MicrosoftLoginController;





Route::get('/', fn() => view('welcome'))->name('home');

Route::get('/dashboard', SgiDashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/cultura', [CulturaController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('cultura.index');

Route::middleware(['auth'])->group(function () {

    Route::post('/soporte/ticket', [SoporteTicketController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('soporte.ticket.store');

    Route::get('/buscar', [BusquedaController::class, 'index'])->name('buscar');

    Route::get('/notificaciones/{id}/ir', [NotificacionController::class, 'ir'])->name('notificaciones.ir');
    Route::post('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas');

    Route::middleware('role:administrador_sgi')->group(function () {
        Route::get('/solicitudes/calendario', [SolicitudesCalendarController::class, 'index'])
            ->name('solicitudes.calendar');

        //solicitud destory
        Route::delete('/solicitudes/{solicitud}', [SolicitudFormatoController::class, 'destroy'])->name('solicitudes.destroy');

        Route::get('/solicitudes/calendario/data', [SolicitudesCalendarController::class, 'data'])
            ->name('solicitudes.calendar.data');

        Route::post('/solicitudes/{solicitud}/sgi/update-version', [SolicitudFormatoController::class, 'sgiUpdateVersion'])
            ->whereNumber('solicitud')
            ->name('solicitudes.sgi.update_version');

        Route::post('/solicitudes/{solicitud}/sgi/update-revision', [SolicitudFormatoController::class, 'sgiUpdateRevision'])
            ->whereNumber('solicitud')
            ->name('solicitudes.sgi.update_revision');

        Route::post('/solicitudes/{solicitud}/sgi/baja', [SolicitudFormatoController::class, 'sgiBaja'])
            ->whereNumber('solicitud')
            ->name('solicitudes.sgi.baja');

        Route::post('/solicitudes/{solicitud}/sgi/reactivar', [SolicitudFormatoController::class, 'sgiReactivar'])
            ->whereNumber('solicitud')
            ->name('solicitudes.sgi.reactivar');
        //marcar obsoleto 

        //botn de como dar de baja  
        Route::post('documentos/{id}/baja', [DocumentoController::class, 'darDeBaja'])->name('documentos.baja');

        Route::get('/documentos/{documento}/edit', [DocumentoController::class, 'edit'])->name('documentos.edit');
        Route::put('/documentos/{documento}', [DocumentoController::class, 'update'])->name('documentos.update');



        // marcar obsoleto para revisiones 

        Route::post(
            '/documento-revisiones/{revision}/marcar-obsoleto',
            [DocumentoRevisionController::class, 'marcarObsoleto']
        )->name('documento_revisiones.marcar_obsoleto');

        Route::post(
            '/documento-versiones/{version}/marcar-obsoleto',
            [DocumentoVersionController::class, 'marcarObsoleto']
        )->name('documento_versiones.marcar_obsoleto');

        Route::post(
            '/documento-versiones/{version}/sharepoint/reintentar',
            [DocumentoVersionController::class, 'reintentarPublicacion']
        )->name('documento_versiones.sharepoint.reintentar');

        Route::get('/documento-versiones/{version}/revisiones/nueva', [DocumentoRevisionController::class, 'create'])
            ->name('documento_versiones.revisiones.create');

        Route::post('/documento-versiones/{version}/revisiones', [DocumentoRevisionController::class, 'store'])
            ->name('documento_versiones.revisiones.store');
    });


    // Solicitud de cambios: restringido a los roles que participan en el flujo
    // (se había perdido este middleware; en `main` envolvía todo este bloque).
    Route::middleware('role:usuario|administrador|administrador_sgi|jefe')->group(function () {

        // ✅ Alias opcional si tú quieres /solicitudes/crear (para tu botón)
        // OJO: el resource default es /solicitudes/create
        Route::get('/solicitudes/crear', fn() => redirect()->route('solicitudes.create'))
            ->name('solicitudes.crear_alias');

        // Formulario usuario/jefe para subir solicitud de actualización
        Route::get('/solicitudes/solicitar-actualizacion', [SolicitudFormatoController::class, 'solicitarActualizacionForm'])
            ->name('solicitudes.solicitar_actualizacion.form');

        Route::post('/solicitudes/solicitar-actualizacion', [SolicitudFormatoController::class, 'solicitarActualizacionStore'])
            ->name('solicitudes.solicitar_actualizacion.store');

        // ===============================
        // SOLICITUDES - RESOURCE (AL FINAL)
        // ===============================
        // ✅ IMPORTANTE: restringimos el parámetro para que NO se coma "calendario" ni "crear"
        Route::get('/solicitudes', [SolicitudFormatoController::class, 'index'])
            ->name('solicitudes.index');

        Route::get('/solicitudes/create', [SolicitudFormatoController::class, 'create'])
            ->name('solicitudes.create');

        Route::post('/solicitudes', [SolicitudFormatoController::class, 'store'])
            ->name('solicitudes.store');

        // ✅ ESTA SIEMPRE AL FINAL
        Route::get('/solicitudes/{solicitud}', [SolicitudFormatoController::class, 'show'])
            ->name('solicitudes.show');

        // Si usas estas pantallas extra:
        Route::get('/solicitudes/{solicitud}/aprobar', [SolicitudFormatoController::class, 'approvalForm'])
            ->whereNumber('solicitud')
            ->name('solicitudes.approval_form');

        Route::post('/solicitudes/{solicitud}/aprobar', [SolicitudFormatoController::class, 'approveOrReject'])
            ->whereNumber('solicitud')
            ->name('solicitudes.approve_or_reject');

        Route::get('/solicitudes/{solicitud}/finalizar', [SolicitudFormatoController::class, 'finalizeForm'])
            ->whereNumber('solicitud')
            ->name('solicitudes.finalize_form');

        Route::post('/solicitudes/{solicitud}/finalizar', [SolicitudFormatoController::class, 'finalize'])
            ->whereNumber('solicitud')
            ->name('solicitudes.finalize');
    });


    // ===============================
    // DOCUMENTOS
    // ===============================
    Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
    Route::get('/documentos/{documento}', [DocumentoController::class, 'show'])->name('documentos.show');

    Route::get('/dashboard-user', [DocumentoUsuarioController::class, 'index'])
        ->name('dashboard.user')
        ->middleware('auth');
    //usuarios

    Route::post('/documentos/{documento}/solicitar-actualizacion', [DocumentoController::class, 'createUpdateRequest'])
        ->name('documentos.solicitar_actualizacion');

    Route::post('/documentos/{documento}/notificar-actualizacion', [DocumentoController::class, 'notifyNeedsUpdate'])
        ->name('documentos.notificar_actualizacion');


    // ===============================
    // USUARIOS (solo admin)
    // ===============================
    Route::middleware('role:administrador')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::put('/usuarios/{user}/toggle-estado', [UserController::class, 'toggleEstado'])->name('usuarios.toggleEstado');
    });

    // ===============================
    // PROFILE
    // ===============================
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    // ===============================
    // ACCIONES CORRECTIVAS
    // ===============================
    Route::prefix('acciones-correctivas')
        ->name('acciones-correctivas.')
        ->group(function () {

            Route::get('/', [AccionCorrectivaController::class, 'index'])
                ->name('index');

            Route::get('/crear', [AccionCorrectivaController::class, 'create'])
                ->name('create');

            Route::post('/', [AccionCorrectivaController::class, 'store'])
                ->name('store');

            Route::get('/{accionCorrectiva}', [AccionCorrectivaController::class, 'show'])
                ->name('show');

            Route::post('/{accionCorrectiva}/estado', [AccionCorrectivaController::class, 'cambiarEstado'])
                ->name('estado.cambiar');

            Route::post('/{accionCorrectiva}/contenciones', [AccionCorrectivaController::class, 'crearContencion'])
                ->name('contenciones.crear');

            Route::get('/{accionCorrectiva}/analisis', [AccionCorrectivaController::class, 'analisis'])
                ->name('analisis');





            Route::post('/{accionCorrectiva}/analisis/iniciar', [AccionCorrectivaController::class, 'iniciarAnalisis'])
                ->name('analisis.iniciar');

            Route::post('/{accionCorrectiva}/analisis/ideas', [AccionCorrectivaController::class, 'agregarIdea'])
                ->name('analisis.idea');

            Route::post(
                '/{accionCorrectiva}/analisis/cinco-porques/{cincoPorque}/pasos',
                [AccionCorrectivaController::class, 'agregarPorque']
            )->name('cinco-porques.paso');

            Route::post(
                '/{accionCorrectiva}/analisis/causa-raiz/{cincoPorque}',
                [AccionCorrectivaController::class, 'proponerCausaRaiz']
            )->name('causa-raiz.proponer');

            Route::post(
                '/{accionCorrectiva}/analisis/causa-raiz/{causaRaiz}/validar',
                [AccionCorrectivaController::class, 'validarCausaRaiz']
            )->name('causa-raiz.validar');

            Route::post(
                '/{accionCorrectiva}/planes-accion',
                [AccionCorrectivaController::class, 'crearPlanAccion']
            )->name('planes.crear');

            Route::post(
                '/{accionCorrectiva}/planes-accion/actividades',
                [AccionCorrectivaController::class, 'agregarActividad']
            )->name('planes.actividades.crear');

            Route::post(
                '/{accionCorrectiva}/verificaciones-cierre',
                [AccionCorrectivaController::class, 'registrarVerificacionCierre']
            )->name('verificaciones-cierre.crear');

            Route::post(
                '/{accionCorrectiva}/esperas-eficacia',
                [AccionCorrectivaController::class, 'iniciarEsperaEficacia']
            )->name('esperas-eficacia.crear');


            Route::post(
                '/{accionCorrectiva}/verificaciones-eficacia',
                [AccionCorrectivaController::class, 'registrarVerificacionEficacia']
            )->name('verificaciones-eficacia.crear');

            Route::post('/{accionCorrectiva}/actividades/{actividad}/evidencias', [AccionCorrectivaController::class, 'crearEvidencia'])->name('actividades.evidencias.crear');

            Route::post('/{accionCorrectiva}/actividades/{actividad}/completar', [AccionCorrectivaController::class, 'completarActividad'])->name('actividades.completar');

            Route::post('/{accionCorrectiva}/analisis/cinco-porques/iniciar', [AccionCorrectivaController::class, 'iniciarCincoPorques'])
                ->name('cinco-porques.iniciar');
        });
    Route::get('/auth/microsoft/redirect', [
        MicrosoftLoginController::class,
        'redirect'
    ])->name('microsoft.redirect');

    Route::get('/auth/microsoft/callback', [
        MicrosoftLoginController::class,
        'callback'
    ])->name('microsoft.callback');
});

require __DIR__ . '/auth.php';
