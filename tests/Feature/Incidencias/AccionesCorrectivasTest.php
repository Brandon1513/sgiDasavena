<?php

use App\Domains\Incidencias\Actions\CambiarEstadoAccionCorrectiva;
use App\Domains\Incidencias\Actions\CrearAccionCorrectiva;
use App\Domains\Incidencias\Actions\CrearContencion;
use App\Domains\Incidencias\Enums\EstadoAccionCorrectiva;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed([
        \Database\Seeders\OrigenAccionCorrectivaSeeder::class,
        \Database\Seeders\EstadoAccionCorrectivaSeeder::class,
    ]);

    $this->usuario = User::factory()->create();
});

test('una acción correctiva no puede saltarse de borrador a plan de acción', function () {
    $crearAC = app(CrearAccionCorrectiva::class);
    $cambiarEstado = app(CambiarEstadoAccionCorrectiva::class);

    $ac = $crearAC->ejecutar([
        'fecha_apertura' => '2026-09-02',
        'origen_id' => 3,
        'responsable_id' => $this->usuario->id,
        'descripcion' => 'AC de prueba para test automatizado.',
    ]);

    expect($ac->estado->codigo)->toBe('borrador');

    expect(fn () =>
        $cambiarEstado->ejecutar(
            $ac,
            EstadoAccionCorrectiva::PLAN_ACCION
        )
    )->toThrow(ValidationException::class);

    $ac->refresh();

    expect($ac->estado->codigo)->toBe('borrador');
});

test('una acción correctiva en contención no puede pasar a análisis sin una contención registrada', function () {
    $crearAC = app(CrearAccionCorrectiva::class);
    $cambiarEstado = app(CambiarEstadoAccionCorrectiva::class);

    $ac = $crearAC->ejecutar([
        'fecha_apertura' => '2026-09-02',
        'origen_id' => 3,
        'responsable_id' => $this->usuario->id,
        'descripcion' => 'AC de prueba de validación de contención.',
    ]);

    $ac = $cambiarEstado->ejecutar(
        $ac,
        EstadoAccionCorrectiva::ABIERTA
    );

    $ac = $cambiarEstado->ejecutar(
        $ac,
        EstadoAccionCorrectiva::CONTENCION
    );

    expect(fn () =>
        $cambiarEstado->ejecutar(
            $ac,
            EstadoAccionCorrectiva::ANALISIS
        )
    )->toThrow(ValidationException::class);
});

test('una acción correctiva en contención puede pasar a análisis cuando existe una contención', function () {
    $crearAC = app(CrearAccionCorrectiva::class);
    $crearContencion = app(CrearContencion::class);
    $cambiarEstado = app(CambiarEstadoAccionCorrectiva::class);

    $ac = $crearAC->ejecutar([
        'fecha_apertura' => '2026-09-02',
        'origen_id' => 3,
        'responsable_id' => $this->usuario->id,
        'descripcion' => 'AC de prueba de transición con contención.',
    ]);

    $ac = $cambiarEstado->ejecutar(
        $ac,
        EstadoAccionCorrectiva::ABIERTA
    );

    $ac = $cambiarEstado->ejecutar(
        $ac,
        EstadoAccionCorrectiva::CONTENCION
    );

    $crearContencion->ejecutar($ac, [
        'descripcion' => 'Contención creada desde prueba automatizada.',
        'responsable_id' => $this->usuario->id,
        'fecha_implementacion' => '2026-09-02',
        'estado' => 'completada',
        'observaciones' => 'Prueba automatizada.',
    ]);

    $ac = $cambiarEstado->ejecutar(
        $ac,
        EstadoAccionCorrectiva::ANALISIS
    );

    expect($ac->estado->codigo)->toBe('analisis');
});