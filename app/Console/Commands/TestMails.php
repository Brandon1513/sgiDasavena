<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use App\Models\SolicitudFormato;
use App\Models\Documento;
use App\Mail\NuevaSolicitudMailable;
use App\Mail\SolicitudAprobadaSgiMailable;
use App\Mail\DocumentoNecesitaActualizacionMailable;
use App\Mail\DivulgacionFormatoMailable;
use App\Mail\DocumentoObsoletoMailable;
use App\Mail\DocumentoAltaMailable;

class TestMails extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Enviar correos de prueba del sistema SGI';

    public function handle()
    {
        $this->info('Enviando correos de prueba...');

        $solicitud = SolicitudFormato::first();
        $documento = Documento::first();

        if (!$solicitud) {
            $this->error('No hay registros en SolicitudFormato');
            return;
        }

        if (!$documento) {
            $this->error('No hay registros en Documento');
            return;
        }

        $correo = 'TU_CORREO@gmail.com';

        try {
            Mail::to($correo)->send(new NuevaSolicitudMailable($solicitud));
            $this->info('✔ NuevaSolicitud enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error NuevaSolicitud: ' . $e->getMessage());
        }

        try {
            Mail::to($correo)->send(new SolicitudAprobadaSgiMailable($solicitud));
            $this->info('✔ Aprobado SGI enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error Aprobado SGI: ' . $e->getMessage());
        }

       
        try {
            Mail::to($correo)->send(
                new DocumentoNecesitaActualizacionMailable(
                    $documento,
                    'Este documento está próximo a vencer',
                    'Sistema SGI'
                )
            );
            $this->info('✔ Necesita actualización enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error actualización: ' . $e->getMessage());
        }

        try {
            Mail::to($correo)->send(new DivulgacionFormatoMailable($solicitud));
            $this->info('✔ Divulgación enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error divulgación: ' . $e->getMessage());
        }

        // 🔥 CAMBIO IMPORTANTE (OBSOLETO)
        try {
            Mail::to($correo)->send(
                new DocumentoObsoletoMailable($documento, auth()->user() ?? (object)[
                    'name' => 'Sistema',
                    'email' => 'sgi@system.com'
                ])
            );
            $this->info('✔ Obsoleto enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error obsoleto: ' . $e->getMessage());
        }

        try {
            Mail::to($correo)->send(new DocumentoAltaMailable($solicitud));
            $this->info('✔ Alta de documento enviado');
        } catch (\Exception $e) {
            $this->error('❌ Error alta de documento: ' . $e->getMessage());
            \Log::error('DocumentoAltaMailable error: ' . $e->getMessage());
        }

        $this->info('Proceso terminado 🚀');
    }
}