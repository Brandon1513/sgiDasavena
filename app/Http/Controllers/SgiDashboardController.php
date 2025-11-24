<?php

namespace App\Http\Controllers;

use App\Models\SolicitudFormato;
use Illuminate\Support\Facades\Auth;

class SgiDashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        // Base query según el rol
        $baseQuery = SolicitudFormato::query();

        // Admin / admin_sgi ven todo
        if ($user->hasRole('administrador') || $user->hasRole('administrador_sgi')) {
            // sin filtros adicionales
        }
        // Jefe ve sus solicitudes y las de su equipo
        elseif ($user->hasRole('jefe')) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('jefe_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        }
        // Usuario normal ve solo las suyas
        else {
            $baseQuery->where('user_id', $user->id);
        }

        // Métricas principales
        $total        = (clone $baseQuery)->count();
        $pendientes   = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $aprobadoJefe = (clone $baseQuery)->where('estado', 'aprobado_jefe')->count();
        $atendidas    = (clone $baseQuery)->where('estado', 'atendido')->count();
        $rechazadas   = (clone $baseQuery)->whereIn('estado', ['rechazado_jefe', 'rechazado_sgi'])->count();

        // Actividad reciente (últimas solicitudes)
        $ultimasSolicitudes = (clone $baseQuery)
            ->with('usuario')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'total'             => $total,
            'pendientes'        => $pendientes,
            'aprobadoJefe'      => $aprobadoJefe,
            'atendidas'         => $atendidas,
            'rechazadas'        => $rechazadas,
            'ultimasSolicitudes'=> $ultimasSolicitudes,
        ]);
    }
}
