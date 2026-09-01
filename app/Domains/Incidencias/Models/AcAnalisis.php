<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AcAnalisis extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_analisis';

    protected $fillable = [
        'accion_correctiva_id',
        'ciclo',
        'fecha_inicio',
        'fecha_cierre',
        'responsable_id',
        'estado',
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
                'fecha_cierre',
                'responsable_id',
                'estado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function ideas(): HasMany
    {
        return $this->hasMany(
            AcIdea::class,
            'ac_analisis_id'
        );
    }
    public function cincoPorques(): HasMany
    {
        return $this->hasMany(
            AcCincoPorque::class,
            'ac_analisis_id'
        );
    }
    public function causasRaiz(): HasMany
    {
        return $this->hasMany(
            AcCausaRaiz::class,
            'ac_analisis_id'
        );
    }
    public function causaRaiz(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(
            AcCausaRaiz::class,
            'ac_cinco_porque_id'
        );
    }
    
}
