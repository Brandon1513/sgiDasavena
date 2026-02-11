<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{
    protected $table =[
        'solicitudes_formatos',
        'user_id',
        'accion',
        'antes',
        'despues',
        'comentario',


    ];
    protected $casts = [
         'antes' => 'array',
         'despues' => 'array',
    ];
}
