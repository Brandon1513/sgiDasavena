<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoRevision extends Model
{
    protected $table = 'documento_revisiones';

    protected $fillable = [
        'documento_version_id',
        'revision_actual',
        'revision_anterior',
        'fecha_revision',
        'vigencia_revision_dias',
        'fecha_vencimiento_revision',
        'liga_archivo',
        'lugar_almacenamiento',
        'estatus',
        'registrado_por',
        'registrado_en',
    ];

    protected $casts = [
        'fecha_revision' => 'date',
        'fecha_vencimiento_revision' => 'date',
        'registrado_en' => 'datetime',
    ];

    public function version()
    {
        return $this->belongsTo(DocumentoVersion::class, 'documento_version_id');
    }
}
