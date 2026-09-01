<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class AccionCorrectiva extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'acciones_correctivas';

    protected $fillable = [
        'codigo',
        'fecha_apertura',
        'origen_id',
        'responsable_id',
        'estado_id',
        'descripcion',
        'porcentaje_avance',
        'fecha_cierre',
        'ciclo_actual',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'date',
            'fecha_cierre' => 'date',
            'porcentaje_avance' => 'decimal:2',
            'ciclo_actual' => 'integer',
        ];
    }

    /**
     * Configuración del Activity Log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'codigo',
                'fecha_apertura',
                'origen_id',
                'responsable_id',
                'estado_id',
                'descripcion',
                'porcentaje_avance',
                'fecha_cierre',
                'ciclo_actual',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(
            OrigenAccionCorrectiva::class,
            'origen_id'
        );
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'responsable_id'
        );
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(
            EstadoAccionCorrectiva::class,
            'estado_id'
        );
    }
    public function contenciones(): HasMany
    {
        return $this->hasMany(
            AcContencion::class,
            'accion_correctiva_id'
        );
    }
    public function verificacionesCierre(): HasMany
    {
        return $this->hasMany(
            AcVerificacionCierre::class,
            'accion_correctiva_id'
        )->orderByDesc('id');
    }
    public function analisis(): HasMany
    {
        return $this->hasMany(
            AcAnalisis::class,
            'accion_correctiva_id'
        );
    }
    public function esperasEficacia(): HasMany
    {
        return $this->hasMany(
            AcEsperaEficacia::class,
            'accion_correctiva_id'
        )->orderByDesc('id');
    }
    public function verificacionesEficacia(): HasMany
    {
        return $this->hasMany(
            AcVerificacionEficacia::class,
            'accion_correctiva_id'
        )->orderByDesc('id');
    }
    public function planesAccion(): HasMany
    {
        return $this->hasMany(
            AcPlanAccion::class,
            'accion_correctiva_id'
        )->orderBy('ciclo');
    }
    public function planAccionActual(): HasOne
    {
        return $this->hasOne(
            AcPlanAccion::class,
            'accion_correctiva_id'
        )->latestOfMany('ciclo');
    }
}
