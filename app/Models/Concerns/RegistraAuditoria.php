<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Auditoría de cambios para el control documental.
 *
 * Registra en activity_log cada alta, cambio y borrado del modelo, con el
 * usuario que lo hizo (causer) y los valores antes/después de los campos
 * de $fillable. Si el cambio lo hace un job o un comando, el causer queda
 * vacío: se interpreta como "Sistema".
 *
 * OJO: solo se registran cambios hechos modelo por modelo
 * ($modelo->update(), ->save(), ::create()). Las actualizaciones masivas
 * (Model::where(...)->update([...]) o $rel()->update([...])) NO disparan
 * eventos y no quedan registradas: en esos casos hay que registrar la
 * acción a mano con activity().
 *
 * Cada modelo define su nombre en singular con:
 *     protected string $auditoriaNombre = 'Documento';
 */
trait RegistraAuditoria
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $nombre = property_exists($this, 'auditoriaNombre') ? $this->auditoriaNombre : class_basename($this);

        return LogOptions::defaults()
            ->useLogName('documentos')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $evento) => match ($evento) {
                    'created' => "Alta: {$nombre}",
                    'updated' => "Modificación: {$nombre}",
                    'deleted' => "Eliminación: {$nombre}",
                    default   => "{$evento}: {$nombre}",
                });
    }
}