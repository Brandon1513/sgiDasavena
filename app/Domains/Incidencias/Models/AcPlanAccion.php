<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcPlanAccion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_planes_accion';

    protected $fillable = [
        'accion_correctiva_id',
        'ac_analisis_id',
        'ciclo',
        'estado',
        'fecha_inicio',
        'fecha_cierre',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'ciclo' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_cierre' => 'date',
        ];
    }

    public function accionCorrectiva(): BelongsTo
    {
        return $this->belongsTo(
            AccionCorrectiva::class,
            'accion_correctiva_id'
        );
    }

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(
            AcAnalisis::class,
            'ac_analisis_id'
        );
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(
            AcActividad::class,
            'ac_plan_accion_id'
        )->orderBy('id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'accion_correctiva_id',
                'ac_analisis_id',
                'ciclo',
                'estado',
                'fecha_inicio',
                'fecha_cierre',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}