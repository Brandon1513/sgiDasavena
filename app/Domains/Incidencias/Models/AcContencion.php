<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcContencion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_contenciones';

    protected $fillable = [
        'accion_correctiva_id',
        'descripcion',
        'responsable_id',
        'fecha_implementacion',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_implementacion' => 'date',
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
                'descripcion',
                'responsable_id',
                'fecha_implementacion',
                'estado',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}