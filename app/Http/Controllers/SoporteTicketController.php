<?php

namespace App\Http\Controllers;

use App\Mail\SoporteTicketMailable;
use App\Models\SoporteTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SoporteTicketController extends Controller
{
    public function store(Request $request): JsonResponse
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

        try {
            Mail::mailer('graph_soporte')
                ->to(config('graph.mail_to_soporte'))
                ->send(new SoporteTicketMailable($ticket));
        } catch (\Throwable $e) {
            $ticket->update([
                'estado' => 'error_envio',
                'error_envio' => $e->getMessage(),
            ]);

            Log::error('No se pudo enviar el correo del ticket de soporte.', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

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
