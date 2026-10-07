<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentoNecesitaActualizacionMailable;

class VerificarVencimientos extends Command
{
    protected $signature = 'documentos:verificar-vencimientos';
    protected $description = 'Verifica documentos por vencer o vencidos (versión y revisión) y envía alertas una vez por vencimiento';

    public function handle()
    {
        $hoy = Carbon::now()->startOfDay();

        $documentos = Documento::with('versionVigente')->get();

        foreach ($documentos as $doc) {
            $version = $doc->versionVigente;

            if (!$version || $version->estatus !== 'vigente') {
                continue;
            }

            $this->revisarVencimiento($doc, $version, 'version', $version->fecha_vencimiento_version, $version->alerta_version_enviada_para, $hoy);
            $this->revisarVencimiento($doc, $version, 'revision', $version->fecha_vencimiento_revision, $version->alerta_revision_enviada_para, $hoy);
        }

        $this->info('Verificación completada');
    }

    private function revisarVencimiento(Documento $doc, DocumentoVersion $version, string $tipo, ?string $fechaVenc, ?string $alertaEnviadaPara, Carbon $hoy): void
    {
        if (!$fechaVenc) {
            return;
        }

        $fechaVenc = Carbon::parse($fechaVenc)->startOfDay();
        $dias = $hoy->diffInDays($fechaVenc, false);

        // Fuera de la ventana de alerta (más de 60 días por vencer)
        if ($dias > 60) {
            return;
        }

        // Ya se avisó para esta misma fecha de vencimiento: no reenviar
        if ($alertaEnviadaPara && Carbon::parse($alertaEnviadaPara)->isSameDay($fechaVenc)) {
            return;
        }

        $asunto = $dias < 0
            ? "Documento vencido ({$tipo})"
            : "Documento próximo a vencer ({$tipo}, {$dias} días)";

        $this->info("{$doc->codigo}: {$asunto}");

        $admins = User::role('administrador_sgi')->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                $this->enviar($admin->email, $doc, $asunto);
            }
        }

        // Sin área en el documento no se notifica a "usuarios del área": de
        // lo contrario where('area', null) termina avisando a cualquier
        // usuario que tampoco tenga área asignada (ajeno al documento).
        if ($doc->area) {
            $usuarios = User::where('area', $doc->area)->get();
            foreach ($usuarios as $usuario) {
                if ($usuario->email) {
                    $this->enviar($usuario->email, $doc, $asunto);
                }
            }
        }

        $version->update([
            "alerta_{$tipo}_enviada_para" => $fechaVenc->toDateString(),
        ]);
    }

    /**
     * Un fallo de SMTP/Graph en un destinatario no debe tumbar el resto de
     * la corrida (otros documentos quedarían sin revisar ese día).
     */
    private function enviar(string $email, Documento $doc, string $asunto): void
    {
        try {
            Mail::to($email)->send(
                new DocumentoNecesitaActualizacionMailable($doc, $asunto, 'Sistema SGI')
            );
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar la alerta de vencimiento de documento.', [
                'documento_id' => $doc->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}