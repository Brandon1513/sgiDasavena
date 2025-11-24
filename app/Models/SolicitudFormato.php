<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudFormato extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_formatos';

    protected $fillable = [
        'user_id',
        'accion',
        'archivo_adjunto',
        'estado',
        'jefe_id',
        'observaciones_jefe',
        'administrador_sgi_id',
        'revision_actual',
        'revision_anterior',
        'liga_archivo',
        'observaciones_sgi', 
        'fecha_alta_sgi', // ← aquí
        'comentarios',
    ];

    // Relación con el usuario que crea la solicitud
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con el jefe
    public function jefe()
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    // Relación con el administrador SGI
    public function administrador_sgi()
    {
        return $this->belongsTo(User::class, 'administrador_sgi_id');
    }
}
