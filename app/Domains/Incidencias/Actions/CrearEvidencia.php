<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcActividad;
use App\Domains\Incidencias\Models\AcEvidencia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CrearEvidencia
{
    public function ejecutar(
        AcActividad $actividad,
        UploadedFile $archivo,
        User $usuario,
        ?string $descripcion = null
    ): AcEvidencia {
        return DB::transaction(function () use (
            $actividad,
            $archivo,
            $usuario,
            $descripcion
        ) {

            /*
             * La actividad debe pertenecer a un plan activo.
             */
            $actividad->loadMissing('planAccion');

            if (!$actividad->planAccion) {
                throw ValidationException::withMessages([
                    'actividad' =>
                        'La actividad no tiene un Plan de Acción asociado.',
                ]);
            }

            if ($actividad->planAccion->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'actividad' =>
                        'No se pueden agregar evidencias a un Plan de Acción cerrado.',
                ]);
            }

            /*
             * Validar archivo.
             */
            $permitidos = [
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx',
                'jpg',
                'jpeg',
                'png',
            ];

            if (!in_array(
                strtolower($archivo->getClientOriginalExtension()),
                $permitidos,
                true
            )) {
                throw ValidationException::withMessages([
                    'archivo' =>
                        'El tipo de archivo no está permitido.',
                ]);
            }

            /*
             * Límite de 20 MB.
             */
            if ($archivo->getSize() > 20 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'archivo' =>
                        'El archivo no puede superar los 20 MB.',
                ]);
            }

            /*
             * Guardar archivo.
             */
            $ruta = $archivo->store(
                'acciones-correctivas/' . $actividad->id,
                'public'
            );

            /*
             * Registrar evidencia.
             */
            return AcEvidencia::create([
                'ac_actividad_id' => $actividad->id,
                'subido_por_id' => $usuario->id,
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta' => $ruta,
                'disco' => 'public',
                'mime_type' => $archivo->getMimeType(),
                'tamano' => $archivo->getSize(),
                'descripcion' => $descripcion,
            ]);
        });
    }
}