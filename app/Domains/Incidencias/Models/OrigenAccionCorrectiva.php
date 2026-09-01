<?php

namespace App\Domains\Incidencias\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrigenAccionCorrectiva extends Model
{
    use HasFactory;

    protected $table = 'origenes_acciones_correctivas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function accionesCorrectivas(): HasMany
    {
        return $this->hasMany(
            AccionCorrectiva::class,
            'origen_id'
        );
    }
}