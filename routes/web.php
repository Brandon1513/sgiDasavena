<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudFormatoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Solicitud de cambios
Route::middleware(['auth', 'role:usuario|administrador|administrador_sgi|jefe'])->group(function () {
    Route::get('/solicitudes', [SolicitudFormatoController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/crear', [SolicitudFormatoController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudFormatoController::class, 'store'])->name('solicitudes.store');
    Route::get('/solicitudes/{solicitud}', [SolicitudFormatoController::class, 'show'])->name('solicitudes.show');
    Route::get('/solicitudes/{solicitud}/aprobar', [SolicitudFormatoController::class, 'approvalForm'])
    ->name('solicitudes.approval_form');
    Route::post('/solicitudes/{solicitud}/aprobar', [SolicitudFormatoController::class, 'approveOrReject'])
    ->name('solicitudes.approve_or_reject');
    Route::get('/solicitudes/{solicitud}/finalizar', [SolicitudFormatoController::class, 'finalizeForm'])
    ->name('solicitudes.finalize_form');

    Route::post('/solicitudes/{solicitud}/finalizar', [SolicitudFormatoController::class, 'finalize'])->name('solicitudes.finalize');

    
});


//Usuarios
use App\Http\Controllers\UserController;

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
    Route::put('/usuarios/{user}/toggle-estado', [UserController::class, 'toggleEstado'])->name('usuarios.toggleEstado');

});

Route::get('/register', function () {
    return redirect()->route('login');
});




require __DIR__.'/auth.php';
