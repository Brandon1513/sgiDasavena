<?php

namespace App\Services\MesaAyuda;

use App\Models\SoporteTicket;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reenvía las solicitudes del chatbot de soporte al sistema de tickets
 * Mesa de Ayuda, autenticado con el token de servicio de la integración
 * (ver `integrations:sgidasavena:token` en ese proyecto).
 */
class MesaAyudaApiClient
{
    /**
     * @return array{ticket_id: int, folio: string}
     */
    public function crearTicket(SoporteTicket $ticket): array
    {
        $baseUrl = config('mesa_ayuda.base_url');
        $token = config('mesa_ayuda.token');

        if (! $baseUrl || ! $token) {
            throw new RuntimeException('La integración con Mesa de Ayuda no está configurada (MESA_AYUDA_BASE_URL / MESA_AYUDA_API_TOKEN).');
        }

        $respuesta = Http::withToken($token)
            ->timeout(10)
            ->post(rtrim($baseUrl, '/').'/api/v1/integrations/sgidasavena/tickets', [
                'requester_email' => $ticket->usuario->email,
                'requester_name' => $ticket->usuario->name,
                'requester_area' => $ticket->usuario->area,
                'tipo' => $ticket->tipo,
                'asunto' => $ticket->asunto,
                'descripcion' => $ticket->descripcion,
                'external_ref' => (string) $ticket->id,
            ]);

        if ($respuesta->failed()) {
            throw new RuntimeException("Mesa de Ayuda respondió con error ({$respuesta->status()}).");
        }

        return [
            'ticket_id' => (int) $respuesta->json('ticket_id'),
            'folio' => (string) $respuesta->json('folio'),
        ];
    }
}
