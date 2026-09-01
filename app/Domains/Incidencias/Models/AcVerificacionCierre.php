<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcVerificacionCierre extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_verificaciones_cierre';

    protected $fillable = [
        'accion_correctiva_id',
        'ciclo',
        'fecha_verificacion',
        'verificado_por_id',
        'acciones_implementadas',
        'evidencias_completas',
        'implementacion_conforme',
        'resultado',
        'resultado_verificacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'ciclo' => 'integer',
            'fecha_verificacion' => 'date',
            'acciones_implementadas' => 'boolean',
            'evidencias_completas' => 'boolean',
            'implementacion_conforme' => 'boolean',
        ];
    }

    /**
     * Acción Correctiva relacionada.
     */
    public function accionCorrectiva(): BelongsTo
    {
        return $this->belongsTo(
            AccionCorrectiva::class,
            'accion_correctiva_id'
        );
    }

    /**
     * Usuario que realizó la verificación.
     */
    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verificado_por_id'
        );
    }

    /**
     * Activity Log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'accion_correctiva_id',
                'ciclo',
                'fecha_verificacion',
                'verificado_por_id',
                'acciones_implementadas',
                'evidencias_completas',
                'implementacion_conforme',
                'resultado',
                'resultado_verificacion',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}