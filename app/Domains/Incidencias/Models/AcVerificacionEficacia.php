<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcVerificacionEficacia extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_verificaciones_eficacia';

    protected $fillable = [
        'accion_correctiva_id',
        'ciclo',
        'fecha_verificacion',
        'verificado_por_id',
        'criterios_cumplidos',
        'resultado_eficaz',
        'resultado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'ciclo' => 'integer',
            'fecha_verificacion' => 'date',
            'criterios_cumplidos' => 'boolean',
            'resultado_eficaz' => 'boolean',
        ];
    }

    public function accionCorrectiva(): BelongsTo
    {
        return $this->belongsTo(
            AccionCorrectiva::class,
            'accion_correctiva_id'
        );
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verificado_por_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'accion_correctiva_id',
                'ciclo',
                'fecha_verificacion',
                'verificado_por_id',
                'criterios_cumplidos',
                'resultado_eficaz',
                'resultado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}