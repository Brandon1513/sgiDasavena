<?php

namespace App\Console\Commands;

use App\Domains\Incidencias\Models\AcActividad;
use App\Mail\ActividadPlanPorVencerMailable;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class VerificarVencimientosAccionesCorrectivas extends Command
{
    protected $signature = 'acciones-correctivas:verificar-vencimientos';
    protected $description = 'Verifica actividades del plan de acción por vencer o vencidas y notifica a su responsable';

    public function handle()
    {
        $hoy = Carbon::now()->startOfDay();

        $actividades = AcActividad::query()
            ->where('estado', '!=', 'completada')
            ->whereNull('fecha_cumplimiento')
            ->with(['responsable', 'planAccion.accionCorrectiva'])
            ->get();

        foreach ($actividades as $actividad) {
            $this->revisarVencimiento($actividad, $hoy);
        }

        $this->info('Verificación completada');
    }

    private function revisarVencimiento(AcActividad $actividad, Carbon $hoy): void
    {
        if (!$actividad->fecha_compromiso || !$actividad->responsable?->email) {
            return;
        }

        $fechaCompromiso = Carbon::parse($actividad->fecha_compromiso)->startOfDay();
        $dias = $hoy->diffInDays($fechaCompromiso, false);

        // Fuera de la ventana de alerta (más de 15 días por vencer)
        if ($dias > 15) {
            return;
        }

        // Ya se avisó para esta misma fecha de compromiso: no reenviar
        if (
            $actividad->alerta_vencimiento_enviada_para
            && Carbon::parse($actividad->alerta_vencimiento_enviada_para)->isSameDay($fechaCompromiso)
        ) {
            return;
        }

        $this->info("{$actividad->planAccion->accionCorrectiva->codigo} / actividad #{$actividad->id}: {$dias} días");

        Mail::to($actividad->responsable->email)->send(
            new ActividadPlanPorVencerMailable($actividad, $dias)
        );

        $actividad->update([
            'alerta_vencimiento_enviada_para' => $fechaCompromiso->toDateString(),
        ]);
    }
}
