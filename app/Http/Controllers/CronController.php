<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Disparadores HTTP para tareas en segundo plano.
 *
 * DomCloud no conserva crontabs, así que un servicio externo (cron-job.org)
 * llama a estas rutas. Se protegen con un token en el encabezado
 * X-Cron-Token, comparado contra config('services.cron.token').
 *
 * No se usa `schedule:run` a propósito: desde una petición web (PHP-FPM),
 * el scheduler arma los comandos con el binario de PHP del proceso actual,
 * que no es el CLI, y las tareas fallan sin aviso. Por eso cada ruta llama
 * directamente al comando que necesita.
 */
class CronController extends Controller
{
    /**
     * Procesa la cola (jobs de SharePoint, correos, etc.).
     * cron-job.org: cada minuto.
     */
    public function cola(Request $request)
    {
        $this->autorizar($request);

        ignore_user_abort(true);
        set_time_limit(90);

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries'           => 3,
            '--max-time'        => 45,
        ]);

        return response('ok', 200);
    }

    /**
     * Tareas diarias de vencimientos.
     * cron-job.org: una vez al día, 08:00 (hora de Guadalajara).
     */
    public function diarios(Request $request)
    {
        $this->autorizar($request);

        ignore_user_abort(true);
        set_time_limit(120);

        foreach ([
            'documentos:verificar-vencimientos',
            'acciones-correctivas:verificar-vencimientos',
        ] as $comando) {
            try {
                Artisan::call($comando);
                Log::info("[cron] {$comando} ejecutado.");
            } catch (\Throwable $e) {
                Log::error("[cron] {$comando} falló.", ['error' => $e->getMessage()]);
            }
        }

        return response('ok', 200);
    }

    private function autorizar(Request $request): void
    {
        $esperado = (string) config('services.cron.token');
        $recibido = (string) $request->header('X-Cron-Token');

        // Sin token configurado, la ruta queda cerrada.
        abort_if($esperado === '' || ! hash_equals($esperado, $recibido), 404);
    }
}