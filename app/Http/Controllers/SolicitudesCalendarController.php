<?php

namespace App\Http\Controllers;

use App\Models\SolicitudFormato;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SolicitudesCalendarController extends Controller
{
    public function index()
    {
        // OJO: esta es la vista que realmente estás usando según TU controller actual
        return view('solicitudes.calendar');
    }

    public function data(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        // filtros
        $q = trim((string) $request->get('q', ''));
        $tipo = $request->get('tipo', 'both'); // both | version | revision

        // ✅ tu front nuevo manda "estado"
        $estado = $request->get('estado', null); // all | vencidos | por_vencer | alerta | en_regla | null

        // ✅ compatibilidad: si tu front viejo manda range=30/60/90/all
        $range = $request->get('range', null); // all | 30 | 60 | 90 | null

        // rango del calendario (solo para pintar cuadritos del mes)
        $gridStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $today = now()->startOfDay();

        // ============================================================
        // 1) TRAER TODO (GLOBAL) - NO filtramos por mes aquí
        // ============================================================
        $rows = SolicitudFormato::query()
            ->where(function ($q2) use ($tipo) {
                if ($tipo === 'version') {
                    $q2->whereNotNull('fecha_vencimiento_version');
                } elseif ($tipo === 'revision') {
                    $q2->whereNotNull('fecha_vencimiento_revision');
                } else {
                    $q2->whereNotNull('fecha_vencimiento_version')
                       ->orWhereNotNull('fecha_vencimiento_revision');
                }
            })
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('codigo_documento', 'like', "%{$q}%")
                      ->orWhere('nombre_documento', 'like', "%{$q}%");
                });
            })
            ->get();

        // ============================================================
        // 2) CONSTRUIR LISTA GLOBAL (flat)
        // ============================================================
        $list = collect();

        foreach ($rows as $s) {
            $base = [
                'id' => $s->id,
                'accion' => $s->accion,
                'estado' => $s->estado,
                'codigo_documento' => $s->codigo_documento,
                'nombre_documento' => $s->nombre_documento,
                'tipo_documento' => $s->tipo_documento,
                'formato_el_pa' => $s->formato_el_pa,
                'folio_version' => $s->folio_version,
                'fecha_version' => $s->fecha_version,
                'vigencia_version_dias' => $s->vigencia_version_dias,
                'vigencia_revision_dias' => $s->vigencia_revision_dias,
                'lugar_almacenamiento' => $s->lugar_almacenamiento,
            ];

            // candidatos según tipo
            $candidatos = [];
            if ($tipo === 'both' || $tipo === 'version') {
                $candidatos[] = ['date' => $s->fecha_vencimiento_version, 'tipo' => 'version'];
            }
            if ($tipo === 'both' || $tipo === 'revision') {
                $candidatos[] = ['date' => $s->fecha_vencimiento_revision, 'tipo' => 'revision'];
            }

            foreach ($candidatos as $item) {
                if (!$item['date']) continue;

                $d = Carbon::parse($item['date'])->startOfDay();
                $days = $today->diffInDays($d, false); // negativo = vencido

                $severity = $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success');

                $list->push(array_merge($base, [
                    'vencimiento_tipo' => $item['tipo'],
                    'fecha_vencimiento' => $d->toDateString(),
                    'days_left' => $days,
                    'severity' => $severity,
                ]));
            }
        }

        // ============================================================
        // 3) STATS GLOBALES (siempre sobre TODO, antes de filtrar estado)
        // ============================================================
        $stats = [
            'critico' => $list->filter(fn($e) => $e['days_left'] <= 30)->count(),            // incluye vencidos
            'alerta'  => $list->filter(fn($e) => $e['days_left'] >= 31 && $e['days_left'] <= 60)->count(),
            'regla'   => $list->filter(fn($e) => $e['days_left'] > 60)->count(),
        ];

        // ============================================================
        // 4) FILTROS PARA LO QUE SE MUESTRA EN LISTA/CALENDARIO
        // ============================================================
        // A) filtro por estado (tu select nuevo)
        if ($estado && $estado !== 'all') {
            $list = $list->filter(function ($e) use ($estado) {
                $d = (int) $e['days_left'];

                return match ($estado) {
                    'vencidos'   => $d < 0,
                    'por_vencer' => $d >= 0 && $d <= 30,
                    'alerta'     => $d >= 31 && $d <= 60,
                    'en_regla'   => $d > 60,
                    default      => true,
                };
            })->values();
        }

        // B) compatibilidad: filtro por range (30/60/90) del front viejo
        //    Solo aplica si NO estás usando estado, o si quieres que se combine.
        if ($range && $range !== 'all' && ctype_digit((string)$range)) {
            $lim = (int) $range;
            $list = $list->filter(fn($e) => (int)$e['days_left'] <= $lim && (int)$e['days_left'] >= 0)->values();
        }

        // Ordenar: más urgente arriba
        $list = $list->sortBy('days_left')->values();

        // ============================================================
        // 5) CALENDARIO: SOLO PINTA EVENTOS QUE CAEN EN EL MES (grid)
        // ============================================================
        $eventsByDate = [];

        foreach ($list as $e) {
            $d = Carbon::parse($e['fecha_vencimiento'])->startOfDay();
            if ($d->betweenIncluded($gridStart, $gridEnd)) {
                $key = $d->toDateString();

                $eventsByDate[$key][] = [
                    'codigo' => $e['codigo_documento'] ?? 'DOC',
                    'nombre' => $e['nombre_documento'],
                    'days_left' => $e['days_left'],
                    'severity' => $e['severity'],
                    'tipo' => $e['vencimiento_tipo'],
                ];
            }
        }

        return response()->json([
            'month' => $month,
            'range' => [
                'start' => $gridStart->toDateString(),
                'end' => $gridEnd->toDateString(),
            ],
            'stats' => $stats,           // ✅ GLOBAL
            'eventsByDate' => $eventsByDate, // ✅ solo lo del mes visible
            'list' => $list,             // ✅ aquí sale TODO según filtros
        ]);
    }
}
