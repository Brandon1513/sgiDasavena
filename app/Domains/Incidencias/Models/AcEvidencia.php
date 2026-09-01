<?php

namespace App\Domains\Incidencias\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AcEvidencia extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_evidencias';

    protected $fillable = [
        'ac_actividad_id',
        'subido_por_id',
        'nombre_original',
        'ruta',
        'disco',
        'mime_type',
        'tamano',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            AcActividad::class,
            'ac_actividad_id'
        );
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'subido_por_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'ac_actividad_id',
                'subido_por_id',
                'nombre_original',
                'ruta',
                'disco',
                'mime_type',
                'tamano',
                'descripcion',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}