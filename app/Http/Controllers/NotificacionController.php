<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class NotificacionController extends Controller
{
    public function ir(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return redirect($notificacion->data['url'] ?? route('dashboard'));
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
