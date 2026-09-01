<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcActividad extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_actividades';

    protected $fillable = [
        'ac_plan_accion_id',
        'descripcion',
        'responsable_id',
        'fecha_compromiso',
        'fecha_cumplimiento',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_compromiso' => 'date',
            'fecha_cumplimiento' => 'date',
        ];
    }

    public function planAccion(): BelongsTo
    {
        return $this->belongsTo(
            AcPlanAccion::class,
            'ac_plan_accion_id'
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
                'ac_plan_accion_id',
                'descripcion',
                'responsable_id',
                'fecha_compromiso',
                'fecha_cumplimiento',
                'estado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function evidencias(): HasMany
{
    return $this->hasMany(
        AcEvidencia::class,
        'ac_actividad_id'
    )->latest();
}
}