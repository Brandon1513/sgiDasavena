<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsecutivoAccionCorrectiva extends Model
{
    use HasFactory;

    protected $table = 'consecutivos_acciones_correctivas';

    protected $fillable = [
        'anio',
        'ultimo_folio',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'ultimo_folio' => 'integer',
        ];
    }
}