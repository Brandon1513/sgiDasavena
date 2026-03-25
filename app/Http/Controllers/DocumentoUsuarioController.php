<?php

namespace App\Http\Controllers;

use App\Models\DocumentoRevision;
use Illuminate\Support\Facades\Auth;

class DocumentoUsuarioController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $estado = request('estado');

        $query = DocumentoRevision::with('version.documento')
            ->where('estatus', 'vigente');

        // 🔒 FILTRO POR ÁREA
        if (!$user->hasRole('administrador') && !$user->hasRole('administrador_sgi')) {
            $query->whereHas('version.documento', function ($q) use ($user) {
                if (!empty($user->area)) {
                    $q->where('area', $user->area);
                }
            });
        }

        $revisiones = $query->get();

        // 🔥 LISTA
        $list = $revisiones->map(function ($rev) {

            $days = $rev->fecha_vencimiento_revision
                ? now()->diffInDays($rev->fecha_vencimiento_revision, false)
                : null;

            return [
                'codigo' => $rev->version->documento->codigo ?? 'N/A',
                'nombre' => $rev->version->documento->nombre ?? 'N/A',
                'version' => $rev->version->version ?? 'N/A',
                'revision' => $rev->revision_actual,
                'area' => $rev->version->documento->area ?? 'N/A',
                'days_left' => $days,
            ];
        });

        // 🔥 STATS
        $stats = [
            'critico' => $list->filter(fn($e) => $e['days_left'] >= 0 && $e['days_left'] <= 30)->count(),
            'alerta'  => $list->filter(fn($e) => $e['days_left'] >= 31 && $e['days_left'] <= 60)->count(),
            'en_regla'=> $list->filter(fn($e) => $e['days_left'] > 60)->count(),
        ];

        // 🔥 FILTRO
        if ($estado && $estado !== 'all') {
            $list = $list->filter(function ($e) use ($estado) {
                $d = (int) $e['days_left'];

                return match ($estado) {
                    'critico' => $d >= 0 && $d <= 30,
                    'alerta' => $d >= 31 && $d <= 60,
                    'en_regla' => $d > 60,
                    default => true,
                };
            })->values();
        }

        $porEstado = collect($stats);

        return view('dashboardUser', compact('list', 'stats', 'porEstado'));
    }
}