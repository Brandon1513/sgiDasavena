<?php

use App\Domains\Incidencias\Models\AcActividad;
use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AcCausaRaiz;
use App\Domains\Incidencias\Models\AcCincoPorque;
use App\Domains\Incidencias\Models\AcIdea;
use App\Domains\Incidencias\Models\AcPlanAccion;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva;
use App\Domains\Incidencias\Models\OrigenAccionCorrectiva;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed([
        \Database\Seeders\RolesSeeder::class,
        \Database\Seeders\OrigenAccionCorrectivaSeeder::class,
        \Database\Seeders\EstadoAccionCorrectivaSeeder::class,
    ]);

    Mail::fake();

    $this->origenId = OrigenAccionCorrectiva::where('nombre', 'Queja de cliente')->value('id');
    $this->responsableAc = User::factory()->create();
});

function crearAcConPlan(int $origenId, int $responsableAcId, string $estadoCodigo = 'plan_accion'): array
{
    $ac = AccionCorrectiva::create([
        'codigo' => '26-' . rand(100, 999),
        'fecha_apertura' => now()->toDateString(),
        'origen_id' => $origenId,
        'responsable_id' => $responsableAcId,
        'estado_id' => EstadoAccionCorrectiva::where('codigo', $estadoCodigo)->value('id'),
        'descripcion' => 'AC de prueba de autorización.',
        'porcentaje_avance' => 0,
        'ciclo_actual' => 1,
    ]);

    $analisis = AcAnalisis::create([
        'accion_correctiva_id' => $ac->id,
        'ciclo' => 1,
        'fecha_inicio' => now()->toDateString(),
        'responsable_id' => $responsableAcId,
        'estado' => 'en_proceso',
    ]);

    $plan = AcPlanAccion::create([
        'accion_correctiva_id' => $ac->id,
        'ac_analisis_id' => $analisis->id,
        'ciclo' => 1,
        'estado' => 'en_proceso',
        'fecha_inicio' => now()->toDateString(),
    ]);

    return [$ac, $analisis, $plan];
}

test('el responsable de una actividad puede subir evidencia aunque no sea el responsable general de la AC', function () {
    [$ac, , $plan] = crearAcConPlan($this->origenId, $this->responsableAc->id);

    $responsableActividad = User::factory()->create();

    $actividad = AcActividad::create([
        'ac_plan_accion_id' => $plan->id,
        'descripcion' => 'Actividad de otro responsable.',
        'responsable_id' => $responsableActividad->id,
        'fecha_compromiso' => now()->addDays(5)->toDateString(),
        'estado' => 'pendiente',
    ]);

    $this->actingAs($responsableActividad)
        ->post(route('acciones-correctivas.actividades.evidencias.crear', [$ac, $actividad]), [
            'archivo' => \Illuminate\Http\UploadedFile::fake()->create('evidencia.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect();

    expect($actividad->evidencias()->count())->toBe(1);
});

test('no se puede gestionar una actividad que pertenece a otra Acción Correctiva', function () {
    [, , $plan1] = crearAcConPlan($this->origenId, $this->responsableAc->id);
    [$ac2] = crearAcConPlan($this->origenId, $this->responsableAc->id);

    $actividad = AcActividad::create([
        'ac_plan_accion_id' => $plan1->id,
        'descripcion' => 'Actividad de la AC 1.',
        'responsable_id' => $this->responsableAc->id,
        'fecha_compromiso' => now()->addDays(5)->toDateString(),
        'estado' => 'pendiente',
    ]);

    // Se intenta completar la actividad de la AC 1 a través de la URL de la AC 2
    $this->actingAs($this->responsableAc)
        ->post(route('acciones-correctivas.actividades.completar', [$ac2, $actividad]), [])
        ->assertNotFound();
});

test('quien propone una causa raíz no puede validarla él mismo', function () {
    [$ac, $analisis] = crearAcConPlan($this->origenId, $this->responsableAc->id, 'analisis');

    $idea = AcIdea::create([
        'ac_analisis_id' => $analisis->id,
        'descripcion' => 'Idea de prueba.',
        'categoria_ishikawa' => 'metodo',
    ]);

    $cincoPorques = AcCincoPorque::create([
        'ac_analisis_id' => $analisis->id,
        'ac_idea_id' => $idea->id,
        'titulo' => 'Cadena de prueba',
        'estado' => 'en_proceso',
    ]);

    $causaRaiz = AcCausaRaiz::create([
        'ac_analisis_id' => $analisis->id,
        'ac_cinco_porque_id' => $cincoPorques->id,
        'descripcion' => 'Causa raíz propuesta por el propio responsable.',
        'propuesta_por_id' => $this->responsableAc->id,
        'fecha_propuesta' => now()->toDateString(),
        'estado_validacion' => 'pendiente',
    ]);

    $this->actingAs($this->responsableAc)
        ->post(route('acciones-correctivas.causa-raiz.validar', [$ac, $causaRaiz]), [
            'aprobada' => true,
        ])
        ->assertForbidden();

    expect($causaRaiz->fresh()->estado_validacion)->toBe('pendiente');
});

test('el responsable de la AC no puede registrar su propia verificación de cierre, solo un administrador', function () {
    [$ac, , $plan] = crearAcConPlan($this->origenId, $this->responsableAc->id, 'ejecucion');
    $ac->update(['porcentaje_avance' => 100]);

    $actividad = AcActividad::create([
        'ac_plan_accion_id' => $plan->id,
        'descripcion' => 'Actividad completada.',
        'responsable_id' => $this->responsableAc->id,
        'fecha_compromiso' => now()->subDays(2)->toDateString(),
        'fecha_cumplimiento' => now()->toDateString(),
        'estado' => 'completada',
    ]);

    $this->actingAs($this->responsableAc)
        ->post(route('acciones-correctivas.verificaciones-cierre.crear', $ac), [
            'acciones_implementadas' => true,
            'evidencias_completas' => true,
            'implementacion_conforme' => true,
            'resultado' => 'Todo conforme.',
        ])
        ->assertForbidden();
});

test('un administrador_sgi sí puede registrar la verificación de cierre', function () {
    [$ac, , $plan] = crearAcConPlan($this->origenId, $this->responsableAc->id, 'ejecucion');
    $ac->update(['porcentaje_avance' => 100]);

    $admin = User::factory()->create();
    $admin->assignRole('administrador_sgi');

    $this->actingAs($admin)
        ->post(route('acciones-correctivas.verificaciones-cierre.crear', $ac), [
            'acciones_implementadas' => true,
            'evidencias_completas' => true,
            'implementacion_conforme' => true,
            'resultado' => 'Todo conforme.',
        ])
        ->assertRedirect();
});

test('un responsable que no pertenece a la AC ni a la actividad no puede completarla', function () {
    [$ac, , $plan] = crearAcConPlan($this->origenId, $this->responsableAc->id);

    $actividad = AcActividad::create([
        'ac_plan_accion_id' => $plan->id,
        'descripcion' => 'Actividad ajena.',
        'responsable_id' => $this->responsableAc->id,
        'fecha_compromiso' => now()->addDays(5)->toDateString(),
        'estado' => 'pendiente',
    ]);

    $ajeno = User::factory()->create();

    $this->actingAs($ajeno)
        ->post(route('acciones-correctivas.actividades.completar', [$ac, $actividad]), [])
        ->assertForbidden();
});
