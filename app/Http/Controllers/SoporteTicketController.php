<?php

namespace App\Http\Controllers;

use App\Mail\SoporteTicketMailable;
use App\Models\SoporteTicket;
use App\Services\MesaAyuda\MesaAyudaApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SoporteTicketController extends Controller
{
    public function store(Request $request, MesaAyudaApiClient $mesaAyuda): JsonResponse
    {
        $data = $request->validate([
            'tipo' => ['required', 'in:ticket,idea'],
            'asunto' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SoporteTicket::create([
            'user_id' => $request->user()->id,
            'tipo' => $data['tipo'],
            'asunto' => $data['asunto'],
            'descripcion' => $data['descripcion'],
        ]);

        $correoFallo = false;

        try {
            Mail::mailer('graph_soporte')
                ->to(config('graph.mail_to_soporte'))
                ->send(new SoporteTicketMailable($ticket));
        } catch (\Throwable $e) {
            $correoFallo = true;

            $ticket->update([
                'estado' => 'error_envio',
                'error_envio' => $e->getMessage(),
            ]);

            Log::error('No se pudo enviar el correo del ticket de soporte.', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Independiente del correo: si Mesa de Ayuda también falla, la
        // solicitud ya quedó registrada de todas formas.
        try {
            $resultado = $mesaAyuda->crearTicket($ticket);

            $ticket->update([
                'mesa_ayuda_ticket_id' => $resultado['ticket_id'],
                'mesa_ayuda_folio' => $resultado['folio'],
                'mesa_ayuda_sync_estado' => 'enviado',
            ]);
        } catch (\Throwable $e) {
            $ticket->update([
                'mesa_ayuda_sync_estado' => 'error',
                'mesa_ayuda_sync_error' => $e->getMessage(),
            ]);

            Log::error('No se pudo crear el ticket en Mesa de Ayuda.', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            // No se le informa al usuario por este error: Mesa de Ayuda es un
            // seguimiento adicional interno, no bloquea su solicitud.
        }

        if ($correoFallo) {
            return response()->json([
                'message' => 'Tu solicitud quedó registrada, pero hubo un problema al notificar a IT por correo. El equipo de soporte le dará seguimiento de todas formas.',
            ], 202);
        }

        return response()->json([
            'message' => $data['tipo'] === 'idea'
                ? '¡Gracias! Tu idea fue enviada al equipo de IT.'
                : 'Tu ticket fue enviado a IT, te contactarán pronto.',
        ]);
    }
}
