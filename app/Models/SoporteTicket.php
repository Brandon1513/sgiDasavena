<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoporteTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo',
        'asunto',
        'descripcion',
        'estado',
        'error_envio',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
