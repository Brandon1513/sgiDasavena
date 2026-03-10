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

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/dashboard', SgiDashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

   
    Route::middleware('role:administrador_sgi')->group(function () {
        Route::get('/solicitudes/calendario', [SolicitudesCalendarController::class, 'index'])
            ->name('solicitudes.calendar');

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
    });

    // ✅ Alias opcional si tú quieres /solicitudes/crear (para tu botón)
    // OJO: el resource default es /solicitudes/create
    Route::get('/solicitudes/crear', fn () => redirect()->route('solicitudes.create'))
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


    // ===============================
    // DOCUMENTOS
    // ===============================
    Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
    Route::get('/documentos/{documento}', [DocumentoController::class, 'show'])->name('documentos.show');

    Route::get('/documentos/{documento}/historico', [DocumentoController::class, 'historico'])
        ->name('documentos.historico');

    // ✅ Compat con tu calendario: route('documentos.versiones.show', $ver->id)

      Route::post('/documento-versiones/{version}/marcar-obsoleto', [DocumentoVersionController::class, 'marcarObsoleto'])
        ->name('documento_versiones.marcar_obsoleto');

    Route::post('/documento-revisiones/{revision}/marcar-obsoleto', [DocumentoRevisionController::class, 'marcarObsoleto'])
        ->name('documento_revisiones.marcar_obsoleto');
        
    Route::get('/documentos/versiones/{version}', [DocumentoVersionController::class, 'show'])
        ->name('documentos.versiones.show');

    Route::get('/documento-versiones/{version}/revisiones/nueva', [DocumentoRevisionController::class, 'create'])
        ->name('documento_versiones.revisiones.create');

    Route::post('/documento-versiones/{version}/revisiones', [DocumentoRevisionController::class, 'store'])
        ->name('documento_versiones.revisiones.store');

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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/register', fn () => redirect()->route('login'));

require __DIR__.'/auth.php';
