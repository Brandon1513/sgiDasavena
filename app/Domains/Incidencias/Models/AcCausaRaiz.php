<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcCausaRaiz extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_causas_raiz';

    protected $fillable = [
        'ac_analisis_id',
        'ac_cinco_porque_id',
        'descripcion',
        'propuesta_por_id',
        'fecha_propuesta',
        'estado_validacion',
        'validado_por_id',
        'fecha_validacion',
        'comentarios_validacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_propuesta' => 'date',
            'fecha_validacion' => 'date',
        ];
    }

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(
            AcAnalisis::class,
            'ac_analisis_id'
        );
    }

    public function cincoPorques(): BelongsTo
    {
        return $this->belongsTo(
            AcCincoPorque::class,
            'ac_cinco_porque_id'
        );
    }

    public function propuestaPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'propuesta_por_id'
        );
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'validado_por_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'ac_analisis_id',
                'ac_cinco_porque_id',
                'descripcion',
                'propuesta_por_id',
                'fecha_propuesta',
                'estado_validacion',
                'validado_por_id',
                'fecha_validacion',
                'comentarios_validacion',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}