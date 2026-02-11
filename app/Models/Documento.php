<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Documento extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_documento',
        'formato_el_pa',
        'area',
        'sharepoint_site',
        'sharepoint_folder',
        'estatus', // vigente | baja
        'version_vigente_id',
    ];


   public function versiones()
{
    return $this->hasMany(\App\Models\DocumentoVersion::class);
}
    public function versionVigente()
    {
        return $this->belongsTo(DocumentoVersion::class, 'version_vigente_id');
    }
    public function getDiasParaVencimientoAttribute(): ?int
    {
        $v = $this->versionVigente;
        if (!$v) return null;

        $today = now()->startOfDay();

        // toma el vencimiento "más cercano" (version o revision) o el que te interese
        $fechas = collect([
            $v->fecha_vencimiento_version,
            $v->fecha_vencimiento_revision,
        ])->filter();

        if ($fechas->isEmpty()) return null;

        $min = $fechas->map(fn($d) => Carbon::parse($d)->startOfDay())->sort()->first();

        return $today->diffInDays($min, false);
    }
    public function getSemaforoVencimientoAttribute(): string
    {
        $days = $this->dias_para_vencimiento;

        if ($days === null) return 'sin_fecha';
        if ($days < 0) return 'vencido';
        if ($days <= 30) return 'critico';
        if ($days <= 60) return 'alerta';
        return 'en_regla';
    }
    public function getEtiquetaVencimientoAttribute(): string
    {
        return match ($this->semaforo_vencimiento) {
            'vencido' => 'Vencido',
            'critico' => 'Por vencer (≤ 30 días)',
            'alerta'  => 'Alerta (31–60 días)',
            'en_regla' => 'En regla (> 60 días)',
            default   => 'Sin fecha',
        };
    }

   
}
