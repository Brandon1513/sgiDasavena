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
        'mesa_ayuda_ticket_id',
        'mesa_ayuda_folio',
        'mesa_ayuda_sync_estado',
        'mesa_ayuda_sync_error',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
