<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\SolicitudFormato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['resultados' => []]);
        }

        $user = $request->user();
        $esAdmin = $user->hasRole('administrador') || $user->hasRole('administrador_sgi');

        $solicitudes = SolicitudFormato::query()
            ->when(!$esAdmin, function ($q) use ($user) {
                if ($user->hasRole('jefe')) {
                    $q->where(fn ($qq) => $qq->where('jefe_id', $user->id)->orWhere('user_id', $user->id));
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->where(function ($q) use ($term) {
                $q->where('accion', 'like', "%{$term}%")
                    ->orWhere('nombre_documento', 'like', "%{$term}%")
                    ->orWhere('codigo_documento', 'like', "%{$term}%");
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (SolicitudFormato $s) => [
                'tipo' => 'Solicitud',
                'titulo' => $s->nombre_documento ?: ucfirst($s->accion),
                'subtitulo' => $s->codigo_documento ? "Código {$s->codigo_documento}" : ucfirst(str_replace('_', ' ', $s->estado)),
                'url' => route('solicitudes.show', $s->id),
            ]);

        $documentos = Documento::query()
            ->when(!$esAdmin && !empty($user->area), fn ($q) => $q->where('area', $user->area))
            ->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhere('nombre', 'like', "%{$term}%");
            })
            ->limit(5)
            ->get()
            ->map(fn (Documento $d) => [
                'tipo' => 'Documento',
                'titulo' => $d->codigo,
                'subtitulo' => $d->nombre,
                'url' => route('documentos.show', $d->id),
            ]);

        return response()->json([
            'resultados' => $solicitudes->concat($documentos)->values(),
        ]);
    }
}
