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
        $tipo = $request->get('tipo', 'both'); // both | version | revision
        $estado = $request->get('estado', 'all'); // all | vencidos | por_vencer | alerta | en_regla
        $historicos = $request->boolean('historicos', false);

        $gridStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $today = now()->startOfDay();

        // 1) Query base: DocumentoVersion (+ Documento)
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

        // 2) Flatten list
        $list = collect();

        foreach ($rows as $ver) {
            $doc = $ver->documento;

            // si por alguna razón no hay doc, salta
            if (!$doc) continue;

            $base = [
                // IDs reales
                'documento_id' => $doc->id,
                'version_id'   => $ver->id,

                // Datos para UI
                'codigo_documento' => $doc->codigo,
                'nombre_documento' => $doc->nombre,
                'tipo_documento'   => $doc->tipo_documento,
                'formato_el_pa'    => $doc->formato_el_pa,
                'folio_version'    => $ver->version,
                'lugar_almacenamiento' => $ver->lugar_almacenamiento,
                'estatus_version'  => $ver->estatus,

                // ✅ URLs (esto arregla el "Ver")
                'url_documento' => route('documentos.show', $doc->id),

                // opcional: si tienes ruta para ver una versión específica
                'url_version'   => route('documentos.versiones.show', $ver->id),
            ];

            // VERSION
            if (($tipo === 'both' || $tipo === 'version') && $ver->fecha_vencimiento_version) {
                $d = Carbon::parse($ver->fecha_vencimiento_version)->startOfDay();
                $days = $today->diffInDays($d, false);
                $severity = $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success');

                $list->push(array_merge($base, [
                    'vencimiento_tipo' => 'version',
                    'fecha_vencimiento' => $d->toDateString(),
                    'days_left' => $days,
                    'severity' => $severity,
                ]));
            }

            // REVISION
            if (($tipo === 'both' || $tipo === 'revision') && $ver->fecha_vencimiento_revision) {
                $d = Carbon::parse($ver->fecha_vencimiento_revision)->startOfDay();
                $days = $today->diffInDays($d, false);
                $severity = $days <= 30 ? 'danger' : ($days <= 60 ? 'warning' : 'success');

                $list->push(array_merge($base, [
                    'vencimiento_tipo' => 'revision',
                    'fecha_vencimiento' => $d->toDateString(),
                    'days_left' => $days,
                    'severity' => $severity,
                ]));
            }
        }

        // 3) Stats
        $stats = [
            'critico' => $list->filter(fn($e) => (int)$e['days_left'] <= 30)->count(),
            'alerta'  => $list->filter(fn($e) => (int)$e['days_left'] >= 31 && (int)$e['days_left'] <= 60)->count(),
            'regla'   => $list->filter(fn($e) => (int)$e['days_left'] > 60)->count(),
        ];

        // 4) filtro por estado
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

        $list = $list->sortBy('days_left')->values();

        // 5) Calendar events (grid visible)
        $eventsByDate = [];

        foreach ($list as $e) {
            $d = Carbon::parse($e['fecha_vencimiento'])->startOfDay();
            if ($d->betweenIncluded($gridStart, $gridEnd)) {
                $eventsByDate[$d->toDateString()][] = [
                    'codigo' => $e['codigo_documento'] ?? 'DOC',
                    'nombre' => $e['nombre_documento'] ?? '',
                    'tipo' => $e['vencimiento_tipo'],
                    'days_left' => $e['days_left'],
                    'severity' => $e['severity'],

                    // ✅ para click en el card
                    'url' => $e['url_documento'] ?? null,
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
}
