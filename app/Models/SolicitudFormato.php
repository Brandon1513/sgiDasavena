<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SolicitudFormato extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_formatos';
protected $fillable = [
    // EXISTENTES (NO SE TOCAN)
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
    'fecha_alta_sgi',
    'comentarios',

    // 🔥 NUEVOS PARA CALENDARIO / SHAREPOINT
    'codigo_documento',
    'tipo_documento',
    'nombre_documento',
    'formato_el_pa',
    'folio_version',
    'fecha_version',
    'vigencia_version_dias',
    'vigencia_revision_dias',
    'fecha_vencimiento_version',
    'fecha_vencimiento_revision',
    'lugar_almacenamiento',
    'motivo_baja',
];
  protected $casts = [
        'fecha_alta_sgi' => 'date',
        'fecha_version' => 'date',
        'fecha_revision' => 'date',
        'fecha_vencimiento_version' => 'date',
        'fecha_vencimiento_revision' => 'date',
    ];
    public function recalcularVencimientos(): void
    {
        // VERSION
        if ($this->fecha_version && $this->vigencia_version_dias) {
            $this->fecha_vencimiento_version = Carbon::parse($this->fecha_version)
                ->startOfDay()
                ->addDays((int) $this->vigencia_version_dias)
                ->toDateString();
        } else {
            $this->fecha_vencimiento_version = null;
        }

        // REVISION
        if ($this->fecha_revision && $this->vigencia_revision_dias) {
            $this->fecha_vencimiento_revision = Carbon::parse($this->fecha_revision)
                ->startOfDay()
                ->addDays((int) $this->vigencia_revision_dias)
                ->toDateString();
        } else {
            $this->fecha_vencimiento_revision = null;
        }
    }

    protected static function booted()
    {
        static::saving(function (SolicitudFormato $s) {
            $dirty = array_keys($s->getDirty());

            $camposQueDisparan = [
                'fecha_version',
                'fecha_revision',
                'vigencia_version_dias',
                'vigencia_revision_dias',
            ];

            if (array_intersect($dirty, $camposQueDisparan)) {
                $s->recalcularVencimientos();
            }
        });
    }


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
