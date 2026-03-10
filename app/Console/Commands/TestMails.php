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
            $this->error('No se encontró ningún registro en SolicitudFormato.');
            return 1;
        }

        if (!$documento) {
            $this->error('No se encontró ningún registro en Documento.');
            return 1;
        }

        Mail::to('test@test.com')->send(new NuevaSolicitudMailable($solicitud));
        Mail::to('test@test.com')->send(new SolicitudAprobadaSgiMailable($solicitud));
        Mail::to('test@test.com')->send(new DocumentoNecesitaActualizacionMailable($documento));
        Mail::to('test@test.com')->send(new DivulgacionFormatoMailable($solicitud));

        $this->info('Correos enviados correctamente.');
        return 0;
    }
}