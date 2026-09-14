<?php

namespace App\Mail;

use App\Models\SoporteTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SoporteTicketMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;

    public function __construct(SoporteTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        $etiqueta = $this->ticket->tipo === 'idea' ? 'Idea de mejora' : 'Ticket de soporte';

        return $this->subject("[{$etiqueta}] {$this->ticket->asunto}")
            ->replyTo($this->ticket->usuario->email, $this->ticket->usuario->name)
            ->markdown('emails.soporte_ticket')
            ->with(['ticket' => $this->ticket]);
    }
}
