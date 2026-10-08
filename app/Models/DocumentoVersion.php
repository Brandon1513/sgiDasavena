<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVersion extends Model
{
    protected $table = 'documento_versiones';

    protected $fillable = [
         'documento_id',
    'version',
    'estatus',
    'revision_actual',
    'revision_anterior',
    'fecha_version',
    'fecha_revision',
    'vigencia_version_dias',
    'vigencia_revision_dias',
    'fecha_vencimiento_version',
    'fecha_vencimiento_revision',
    'alerta_version_enviada_para',
    'alerta_revision_enviada_para',
    'liga_archivo',
    'lugar_almacenamiento',
    'archivo_storage',
    'sharepoint_file_id',
    'sharepoint_path',
    'publicado_por',
    'publicado_en',
    'observaciones_sgi',
    'sp_drive_id',
    'sp_item_id',
    'sp_web_url',
    'sp_folder_path',
    'sp_estado',
    'sp_error',
    'fecha_publicacion',
    ];

    protected $casts = [
        'fecha_version' => 'date',
        'fecha_revision' => 'date',
        'fecha_vencimiento_version' => 'date',
        'fecha_vencimiento_revision' => 'date',
        'alerta_version_enviada_para' => 'date',
        'alerta_revision_enviada_para' => 'date',
        'publicado_en' => 'datetime',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }
    
public function revisiones()
{
    return $this->hasMany(\App\Models\DocumentoRevision::class, 'documento_version_id');
}

public function ultimaRevision()
{
    return $this->hasOne(\App\Models\DocumentoRevision::class, 'documento_version_id')
        ->latestOfMany();
}
}
