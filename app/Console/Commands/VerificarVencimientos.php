<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentoNecesitaActualizacionMailable;

class VerificarVencimientos extends Command
{
    protected $signature = 'documentos:verificar-vencimientos';
    protected $description = 'Verifica documentos por vencer y envía alertas';

    public function handle()
    {
        $hoy = Carbon::now();

        $documentos = Documento::with('versionVigente')->get();

        foreach ($documentos as $doc) {

            $version = $doc->versionVigente;

            if (!$version || $version->estatus !== 'vigente') {
                continue;
            }

            $fechaVenc = $version->fecha_vencimiento_version;

            if (!$fechaVenc) continue;

            $dias = $hoy->diffInDays($fechaVenc, false);

            // 🔥 CRÍTICO (≤ 30 días)
            if ($dias <= 30 && $dias >= 0) {

                $this->info("Documento crítico: {$doc->codigo}");

                // 1. Admins SGI
                $admins = User::role('administrador_sgi')->get();

                foreach ($admins as $admin) {
                    if ($admin->email) {
                        Mail::to($admin->email)->send(
                            new DocumentoNecesitaActualizacionMailable(
                                $doc,
                                'Documento próximo a vencer',
                                'Sistema SGI'
                            )
                        );
                    }
                }

                // 2. Usuarios del área
                $usuarios = User::where('area', $doc->area)->get();

                foreach ($usuarios as $usuario) {
                    if ($usuario->email) {
                        Mail::to($usuario->email)->send(
                            new DocumentoNecesitaActualizacionMailable(
                                $doc,
                                'Documento próximo a vencer en tu área',
                                'Sistema SGI'
                            )
                        );
                    }
                }
            }
        }

        $this->info('Verificación completada');
    }
}
