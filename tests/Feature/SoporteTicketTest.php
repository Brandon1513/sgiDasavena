<?php

use App\Models\SoporteTicket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    config([
        'mesa_ayuda.base_url' => 'http://mesa-ayuda.test',
        'mesa_ayuda.token' => 'token-de-prueba',
    ]);

    $this->usuario = User::factory()->create(['email' => 'empleado@dasavena.com']);
});

test('al crear un ticket se reenvía a Mesa de Ayuda y se guarda el folio', function () {
    Http::fake([
        'mesa-ayuda.test/*' => Http::response(['ticket_id' => 7, 'folio' => 'TKT-2026-000007'], 201),
    ]);

    $this->actingAs($this->usuario)->postJson(route('soporte.ticket.store'), [
        'tipo' => 'ticket',
        'asunto' => 'La impresora no imprime',
        'descripcion' => 'Se atoró el papel varias veces.',
    ])->assertOk();

    $ticket = SoporteTicket::firstOrFail();

    expect($ticket->mesa_ayuda_sync_estado)->toBe('enviado')
        ->and($ticket->mesa_ayuda_ticket_id)->toBe(7)
        ->and($ticket->mesa_ayuda_folio)->toBe('TKT-2026-000007');

    Http::assertSent(function ($request) use ($ticket) {
        return $request->url() === 'http://mesa-ayuda.test/api/v1/integrations/sgidasavena/tickets'
            && $request->hasHeader('Authorization', 'Bearer token-de-prueba')
            && $request['requester_email'] === 'empleado@dasavena.com'
            && $request['external_ref'] === (string) $ticket->id;
    });
});

test('si Mesa de Ayuda falla, la solicitud queda registrada y la respuesta al usuario no cambia', function () {
    Http::fake([
        'mesa-ayuda.test/*' => Http::response(['message' => 'error'], 500),
    ]);

    $respuesta = $this->actingAs($this->usuario)->postJson(route('soporte.ticket.store'), [
        'tipo' => 'idea',
        'asunto' => 'Sugerencia',
        'descripcion' => 'Sería bueno tener modo oscuro.',
    ]);

    $respuesta->assertOk()->assertJson(['message' => '¡Gracias! Tu idea fue enviada al equipo de IT.']);

    $ticket = SoporteTicket::firstOrFail();

    expect($ticket->mesa_ayuda_sync_estado)->toBe('error')
        ->and($ticket->mesa_ayuda_sync_error)->not->toBeNull();
});
