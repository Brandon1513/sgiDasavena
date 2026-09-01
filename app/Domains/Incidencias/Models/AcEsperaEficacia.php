<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcEsperaEficacia extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_esperas_eficacia';

    protected $fillable = [
        'accion_correctiva_id',
        'ciclo',
        'fecha_inicio',
        'fecha_verificacion',
        'responsable_id',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'ciclo' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_verificacion' => 'date',
        ];
    }

    public function accionCorrectiva(): BelongsTo
    {
        return $this->belongsTo(
            AccionCorrectiva::class,
            'accion_correctiva_id'
        );
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'responsable_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'accion_correctiva_id',
                'ciclo',
                'fecha_inicio',
                'fecha_verificacion',
                'responsable_id',
                'estado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}