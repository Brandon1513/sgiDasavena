<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcCincoPorquePaso extends Model
{
    use HasFactory;

    protected $table = 'ac_cinco_porques_pasos';

    protected $fillable = [
        'ac_cinco_porque_id',
        'numero',
        'pregunta',
        'respuesta',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
        ];
    }

    public function cincoPorque(): BelongsTo
    {
        return $this->belongsTo(
            AcCincoPorque::class,
            'ac_cinco_porque_id'
        );
    }
}