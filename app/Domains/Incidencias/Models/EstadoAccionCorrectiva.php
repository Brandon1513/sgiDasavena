<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoAccionCorrectiva extends Model
{
    use HasFactory;

    protected $table = 'estados_acciones_correctivas';

    protected $fillable = [
        'codigo',
        'nombre',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function accionesCorrectivas(): HasMany
    {
        return $this->hasMany(
            AccionCorrectiva::class,
            'estado_id'
        );
    }
}