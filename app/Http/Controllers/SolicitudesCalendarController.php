<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DocumentoVersion;

class SolicitudesCalendarController extends Controller
{
    public function index()
    {
        return view('solicitudes.calendar');
    }
public function data(Request $request)
{
    $month = $request->get('month', now()->format('Y-m'));
    $q = trim((string) $request->get('q', ''));
    $tipo = $request->get('tipo', 'both'); 
    $estado = $request->get('estado', 'all'); 
    $historicos = $request->boolean('historicos', false);

    $gridStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
    $gridEnd   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

    $today = now()->startOfDay();

    $rows = DocumentoVersion::query()
        ->with(['documento'])
        ->when(!$historicos, function ($qq) {
            $qq->where('estatus', 'vigente');
        })
        ->when($q !== '', function ($qq) use ($q) {
            $qq->whereHas('documento', function ($w) use ($q) {
                $w->where('codigo', 'like', "%{$q}%")
                  ->orWhere('nombre', 'like', "%{$q}%");
            });
        })
        ->get();

    $list = collect();

    foreach ($rows as $ver) {
        $doc = $ver->documento;
        if (!$doc) continue;

        $base = [
            'documento_id' => $doc->id,
            'version_id'   => $ver->id,
            'codigo_documento' => $doc->codigo,
            'nombre_documento' => $doc->nombre,
            'tipo_documento'   => $doc->tipo_documento,
            'formato_el_pa'    => $doc->formato_el_pa,
            'folio_version'    => $ver->version,
            'url_documento' => route('documentos.show', $doc->id),
        ];

        // VERSION
        if (($tipo === 'both' || $tipo === 'version') && $ver->fecha_vencimiento_version) {
            $d = Carbon::parse($ver->fecha_vencimiento_version)->startOfDay();
            $days = $today->diffInDays($d, false);
            
            $list->push(array_merge($base, [
                'vencimiento_tipo' => 'version',
                'fecha_vencimiento' => $d->toDateString(),
                'days_left' => (int)$days,
                'severity' => $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success'),
            ]));
        }

        // REVISION
        if (($tipo === 'both' || $tipo === 'revision') && $ver->fecha_vencimiento_revision) {
            $d = Carbon::parse($ver->fecha_vencimiento_revision)->startOfDay();
            $days = $today->diffInDays($d, false);

            $list->push(array_merge($base, [
                'vencimiento_tipo' => 'revision',
                'fecha_vencimiento' => $d->toDateString(),
                'days_left' => (int)$days,
                'severity' => $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success'),
            ]));
        }
    }

    // --- ESTADÍSTICAS (Comparado con tu lógica anterior y corregido) ---
    // Aseguramos que 'regla' cuente a todos los que tienen más de 60 días
    $stats = [
        'critico' => $list->filter(fn($e) => $e['days_left'] <= 30)->count(),
        'alerta'  => $list->filter(fn($e) => $e['days_left'] >= 31 && $e['days_left'] <= 60)->count(),
        'regla'   => $list->filter(fn($e) => $e['days_left'] > 60)->count(),
        // Agregamos 'vencidos' explícitamente por si tu vista lo usa
        'vencidos' => $list->filter(fn($e) => $e['days_left'] < 0)->count(),
    ];

    // --- FILTRADO POR ESTADO ---
    if ($estado && $estado !== 'all') {
        $list = $list->filter(function ($e) use ($estado) {
            $d = (int)$e['days_left'];
            return match ($estado) {
                'vencidos'   => $d < 0,
                'por_vencer' => $d >= 0 && $d <= 30, // Estos son los 'criticos' pero no vencidos
                'alerta'     => $d >= 31 && $d <= 60,
                'en_regla'   => $d > 60, // Aquí es donde entran los que "están bien"
                default      => true,
            };
        })->values();
    }

    $list = $list->sortBy('days_left')->values();

    // --- EVENTOS PARA EL CALENDARIO ---
    $eventsByDate = [];
    foreach ($list as $e) {
        $d = Carbon::parse($e['fecha_vencimiento'])->startOfDay();
        if ($d->betweenIncluded($gridStart, $gridEnd)) {
            $eventsByDate[$d->toDateString()][] = [
                'codigo' => $e['codigo_documento'],
                'nombre' => $e['nombre_documento'],
                'tipo' => $e['vencimiento_tipo'],
                'days_left' => $e['days_left'],
                'severity' => $e['severity'],
                'url' => $e['url_documento'],
            ];
        }
    }

    return response()->json([
        'month' => $month,
        'stats' => $stats,
        'eventsByDate' => $eventsByDate,
        'list' => $list,
    ]);
}


// Función auxiliar para mantener limpio el código
private function getSeverity($days) {
    if ($days < 0) return 'danger'; // Ya venció
    if ($days <= 30) return 'danger'; // Crítico
    if ($days <= 60) return 'warning'; // Alerta
    return 'success'; // En regla
}
}
