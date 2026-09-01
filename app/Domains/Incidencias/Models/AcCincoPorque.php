<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcCincoPorque extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'ac_cinco_porques';

    protected $fillable = [
        'ac_analisis_id',
        'ac_idea_id',
        'titulo',
        'causa_raiz_propuesta',
        'estado',
    ];

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(
            AcAnalisis::class,
            'ac_analisis_id'
        );
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(
            AcIdea::class,
            'ac_idea_id'
        );
    }

    public function pasos(): HasMany
    {
        return $this->hasMany(
            AcCincoPorquePaso::class,
            'ac_cinco_porque_id'
        )->orderBy('numero');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('acciones_correctivas')
            ->logOnly([
                'ac_analisis_id',
                'ac_idea_id',
                'titulo',
                'causa_raiz_propuesta',
                'estado',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function causaRaiz(): HasOne
    {
        return $this->hasOne(
            AcCausaRaiz::class,
            'ac_cinco_porque_id'
        );
    }
}
