<?php

use App\Domains\Incidencias\Models\AcActividad;
use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AcEsperaEficacia;
use App\Domains\Incidencias\Models\AcEvidencia;
use App\Domains\Incidencias\Models\AcPlanAccion;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva;
use App\Domains\Incidencias\Models\OrigenAccionCorrectiva;
use App\Mail\AccionCorrectivaAsignadaMailable;
use App\Mail\AccionCorrectivaEstadoCambiadoMailable;
use App\Mail\AccionCorrectivaVerificacionMailable;
use App\Mail\ActividadPlanAsignadaMailable;
use App\Mail\ActividadPlanPorVencerMailable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed([
        \Database\Seeders\RolesSeeder::class,
        \Database\Seeders\OrigenAccionCorrectivaSeeder::class,
        \Database\Seeders\EstadoAccionCorrectivaSeeder::class,
    ]);

    Mail::fake();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('administrador');

    $this->responsable = User::factory()->create();

    $this->origenId = OrigenAccionCorrectiva::where('nombre', 'Queja de cliente')->value('id');
});

test('crear una Acción Correctiva notifica por correo al responsable', function () {
    $respuesta = $this->actingAs($this->admin)->post(route('acciones-correctivas.store'), [
        'descripcion' => 'AC de prueba para notificaciones.',
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
    ]);

    $respuesta->assertRedirect();

    $ac = AccionCorrectiva::firstOrFail();

    Mail::assertSent(AccionCorrectivaAsignadaMailable::class, function ($mail) use ($ac) {
        return $mail->hasTo($this->responsable->email)
            && $mail->accionCorrectiva->id === $ac->id;
    });
});

test('cambiar de estado notifica por correo al responsable con el estado anterior y el nuevo', function () {
    $ac = AccionCorrectiva::create([
        'codigo' => '26-900',
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', 'borrador')->value('id'),
        'descripcion' => 'AC para probar cambio de estado.',
        'porcentaje_avance' => 0,
        'ciclo_actual' => 1,
    ]);

    $this->actingAs($this->admin)
        ->post(route('acciones-correctivas.estado.cambiar', $ac), ['estado' => 'abierta'])
        ->assertRedirect();

    Mail::assertSent(AccionCorrectivaEstadoCambiadoMailable::class, function ($mail) use ($ac) {
        return $mail->hasTo($this->responsable->email)
            && $mail->accionCorrectiva->id === $ac->id
            && $mail->estadoAnterior === 'Borrador'
            && $mail->estadoNuevo === 'Abierta';
    });
});

test('agregar una actividad al plan de acción notifica a su responsable, no al de la AC', function () {
    $ac = AccionCorrectiva::create([
        'codigo' => '26-901',
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', 'plan_accion')->value('id'),
        'descripcion' => 'AC para probar actividad.',
        'porcentaje_avance' => 0,
        'ciclo_actual' => 1,
    ]);

    $analisis = AcAnalisis::create([
        'accion_correctiva_id' => $ac->id,
        'ciclo' => 1,
        'fecha_inicio' => now()->toDateString(),
        'responsable_id' => $this->responsable->id,
        'estado' => 'en_proceso',
    ]);

    AcPlanAccion::create([
        'accion_correctiva_id' => $ac->id,
        'ac_analisis_id' => $analisis->id,
        'ciclo' => 1,
        'estado' => 'en_proceso',
        'fecha_inicio' => now()->toDateString(),
    ]);

    $responsableActividad = User::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('acciones-correctivas.planes.actividades.crear', $ac), [
            'descripcion' => 'Actualizar el procedimiento documentado.',
            'responsable_id' => $responsableActividad->id,
            'fecha_compromiso' => now()->addDays(5)->toDateString(),
        ])
        ->assertRedirect();

    Mail::assertSent(ActividadPlanAsignadaMailable::class, function ($mail) use ($responsableActividad) {
        return $mail->hasTo($responsableActividad->email);
    });

    Mail::assertNotSent(ActividadPlanAsignadaMailable::class, function ($mail) {
        return $mail->hasTo($this->responsable->email);
    });
});

test('registrar una verificación de cierre notifica al responsable de la AC', function () {
    $ac = AccionCorrectiva::create([
        'codigo' => '26-902',
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', 'ejecucion')->value('id'),
        'descripcion' => 'AC para probar verificación de cierre.',
        'porcentaje_avance' => 100,
        'ciclo_actual' => 1,
    ]);

    $analisis = AcAnalisis::create([
        'accion_correctiva_id' => $ac->id,
        'ciclo' => 1,
        'fecha_inicio' => now()->toDateString(),
        'responsable_id' => $this->responsable->id,
        'estado' => 'en_proceso',
    ]);

    $plan = AcPlanAccion::create([
        'accion_correctiva_id' => $ac->id,
        'ac_analisis_id' => $analisis->id,
        'ciclo' => 1,
        'estado' => 'completado',
        'fecha_inicio' => now()->toDateString(),
    ]);

    $actividad = AcActividad::create([
        'ac_plan_accion_id' => $plan->id,
        'descripcion' => 'Actividad completada.',
        'responsable_id' => $this->responsable->id,
        'fecha_compromiso' => now()->subDays(2)->toDateString(),
        'fecha_cumplimiento' => now()->toDateString(),
        'estado' => 'completada',
    ]);

    AcEvidencia::create([
        'ac_actividad_id' => $actividad->id,
        'subido_por_id' => $this->responsable->id,
        'nombre_original' => 'evidencia.pdf',
        'ruta' => 'evidencias/evidencia.pdf',
    ]);

    $this->actingAs($this->admin)
        ->post(route('acciones-correctivas.verificaciones-cierre.crear', $ac), [
            'acciones_implementadas' => true,
            'evidencias_completas' => true,
            'implementacion_conforme' => true,
            'resultado' => 'Todo conforme.',
        ])
        ->assertRedirect();

    Mail::assertSent(AccionCorrectivaVerificacionMailable::class, function ($mail) use ($ac) {
        return $mail->hasTo($this->responsable->email)
            && $mail->accionCorrectiva->id === $ac->id
            && $mail->tipo === 'cierre';
    });
});

test('registrar una verificación de eficacia notifica al responsable de la AC', function () {
    $ac = AccionCorrectiva::create([
        'codigo' => '26-903',
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', 'verificacion_eficacia')->value('id'),
        'descripcion' => 'AC para probar verificación de eficacia.',
        'porcentaje_avance' => 100,
        'ciclo_actual' => 1,
    ]);

    AcEsperaEficacia::create([
        'accion_correctiva_id' => $ac->id,
        'ciclo' => 1,
        'fecha_inicio' => now()->subDays(10)->toDateString(),
        'fecha_verificacion' => now()->subDay()->toDateString(),
        'responsable_id' => $this->responsable->id,
        'estado' => 'lista_verificacion',
    ]);

    $this->actingAs($this->admin)
        ->post(route('acciones-correctivas.verificaciones-eficacia.crear', $ac), [
            'criterios_cumplidos' => true,
            'resultado_eficaz' => true,
            'resultado' => 'La acción fue eficaz.',
        ])
        ->assertRedirect();

    Mail::assertSent(AccionCorrectivaVerificacionMailable::class, function ($mail) use ($ac) {
        return $mail->hasTo($this->responsable->email)
            && $mail->accionCorrectiva->id === $ac->id
            && $mail->tipo === 'eficacia';
    });
});

test('el comando de vencimientos notifica una actividad vencida y no la reenvía en la misma corrida de días', function () {
    $ac = AccionCorrectiva::create([
        'codigo' => '26-904',
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $this->origenId,
        'responsable_id' => $this->responsable->id,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', 'plan_accion')->value('id'),
        'descripcion' => 'AC para probar el recordatorio de vencimiento.',
        'porcentaje_avance' => 0,
        'ciclo_actual' => 1,
    ]);

    $analisis = AcAnalisis::create([
        'accion_correctiva_id' => $ac->id,
        'ciclo' => 1,
        'fecha_inicio' => now()->toDateString(),
        'responsable_id' => $this->responsable->id,
        'estado' => 'en_proceso',
    ]);

    $plan = AcPlanAccion::create([
        'accion_correctiva_id' => $ac->id,
        'ac_analisis_id' => $analisis->id,
        'ciclo' => 1,
        'estado' => 'en_proceso',
        'fecha_inicio' => now()->toDateString(),
    ]);

    AcActividad::create([
        'ac_plan_accion_id' => $plan->id,
        'descripcion' => 'Actividad vencida sin cumplir.',
        'responsable_id' => $this->responsable->id,
        'fecha_compromiso' => now()->subDays(3)->toDateString(),
        'estado' => 'pendiente',
    ]);

    $this->artisan('acciones-correctivas:verificar-vencimientos');

    Mail::assertSent(ActividadPlanPorVencerMailable::class, function ($mail) {
        return $mail->hasTo($this->responsable->email) && $mail->dias < 0;
    });

    $this->artisan('acciones-correctivas:verificar-vencimientos');

    Mail::assertSent(ActividadPlanPorVencerMailable::class, 1);
});
