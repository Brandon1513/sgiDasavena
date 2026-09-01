<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcIdea extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_ideas';

    protected $fillable = [
        'ac_analisis_id',
        'descripcion',
        'categoria_ishikawa',
        'es_causa_probable',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'es_causa_probable' => 'boolean',
        ];
    }

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(
            AcAnalisis::class,
            'ac_analisis_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'ac_analisis_id',
                'descripcion',
                'categoria_ishikawa',
                'es_causa_probable',
                'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public function cincoPorques(): HasMany
    {
        return $this->hasMany(
            AcCincoPorque::class,
            'ac_idea_id'
        );
    }
}
