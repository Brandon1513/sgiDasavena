<?php

namespace App\Http\Controllers;

use App\Models\SolicitudFormato;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SolicitudesCalendarController extends Controller
{
    public function index(Request $request)
    {
        return view('solicitudes.calendario_vencimientos');
    }

    public function data(Request $request)
    {
        $month  = $request->query('month', now()->format('Y-m'));
        $q      = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', 'all'); // all | vencidos | por_vencer | alerta | en_regla
        $tipo   = $request->query('tipo', 'both');  // both | version | revision

        $gridStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $today = now()->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | 1) STATS GLOBALES (no dependen del mes ni del filtro estado)
        |--------------------------------------------------------------------------
        */
        $allRows = SolicitudFormato::query()
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
            // Si quieres contar SOLO docs ya publicados en SGI:
            // ->where('estado', 'atendido')
            ->get();

        $statsCritico = 0; // vencidos o <=30
        $statsAlerta  = 0; // 31-60
        $statsRegla   = 0; // >60

        foreach ($allRows as $s) {
            if (($tipo === 'both' || $tipo === 'version') && $s->fecha_vencimiento_version) {
                $days = $today->diffInDays(Carbon::parse($s->fecha_vencimiento_version)->startOfDay(), false);
                if ($days <= 30) $statsCritico++;
                elseif ($days <= 60) $statsAlerta++;
                else $statsRegla++;
            }

            if (($tipo === 'both' || $tipo === 'revision') && $s->fecha_vencimiento_revision) {
                $days = $today->diffInDays(Carbon::parse($s->fecha_vencimiento_revision)->startOfDay(), false);
                if ($days <= 30) $statsCritico++;
                elseif ($days <= 60) $statsAlerta++;
                else $statsRegla++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2) EVENTOS (lista) => respeta q + tipo + estado
        |--------------------------------------------------------------------------
        */
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
            // Si quieres mostrar SOLO docs ya publicados en SGI:
            // ->where('estado', 'atendido')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('codigo_documento', 'like', "%{$q}%")
                      ->orWhere('nombre_documento', 'like', "%{$q}%");
                });
            })
            ->get();

        $events = [];

        foreach ($rows as $s) {
            $codigo = $s->codigo_documento ?: ($s->nombre_documento ?: 'Documento');

            // VERSION
            if (($tipo === 'both' || $tipo === 'version') && $s->fecha_vencimiento_version) {
                $date = Carbon::parse($s->fecha_vencimiento_version)->startOfDay();
                $daysLeft = $today->diffInDays($date, false);

                $events[] = [
                    'id' => $s->id,
                    'vencimiento_tipo' => 'version',
                    'fecha_vencimiento' => $date->toDateString(),
                    'days_left' => $daysLeft,

                    'codigo_documento' => $codigo,
                    'nombre_documento' => $s->nombre_documento,
                    'tipo_documento' => $s->tipo_documento,
                    'formato_el_pa' => $s->formato_el_pa,
                    'folio_version' => $s->folio_version,
                    'lugar_almacenamiento' => $s->lugar_almacenamiento,

                    'severity' => $this->severity($daysLeft),
                ];
            }

            // REVISION
            if (($tipo === 'both' || $tipo === 'revision') && $s->fecha_vencimiento_revision) {
                $date = Carbon::parse($s->fecha_vencimiento_revision)->startOfDay();
                $daysLeft = $today->diffInDays($date, false);

                $events[] = [
                    'id' => $s->id,
                    'vencimiento_tipo' => 'revision',
                    'fecha_vencimiento' => $date->toDateString(),
                    'days_left' => $daysLeft,

                    'codigo_documento' => $codigo,
                    'nombre_documento' => $s->nombre_documento,
                    'tipo_documento' => $s->tipo_documento,
                    'formato_el_pa' => $s->formato_el_pa,
                    'folio_version' => $s->folio_version,
                    'lugar_almacenamiento' => $s->lugar_almacenamiento,

                    'severity' => $this->severity($daysLeft),
                ];
            }
        }

        // ✅ filtro por ESTADO
        $events = array_values(array_filter($events, function ($e) use ($estado) {
            $d = (int) $e['days_left'];

            return match ($estado) {
                'vencidos'   => $d < 0,
                'por_vencer' => $d >= 0 && $d <= 30,
                'alerta'     => $d >= 31 && $d <= 60,
                'en_regla'   => $d > 60,
                default      => true, // all
            };
        }));

        // ✅ Ordenar por days_left asc (más urgente arriba)
        usort($events, fn($a, $b) => $a['days_left'] <=> $b['days_left']);

        /*
        |--------------------------------------------------------------------------
        | 3) CALENDARIO (solo pinta lo del mes visible)
        |--------------------------------------------------------------------------
        */
        $eventsByDate = [];

        foreach ($events as $e) {
            $d = Carbon::parse($e['fecha_vencimiento'])->startOfDay();
            if ($d->betweenIncluded($gridStart, $gridEnd)) {
                $key = $d->toDateString();
                $eventsByDate[$key][] = [
                    'id' => $e['id'] . '-' . ($e['vencimiento_tipo'] === 'version' ? 'ver' : 'rev'),
                    'codigo' => $e['codigo_documento'],
                    'nombre' => $e['nombre_documento'],
                    'tipo' => $e['vencimiento_tipo'],
                    'days_left' => $e['days_left'],
                    'severity' => $e['severity'],
                ];
            }
        }

        return response()->json([
            'month' => $month,
            'range' => [
                'start' => $gridStart->toDateString(),
                'end' => $gridEnd->toDateString(),
            ],
            'stats' => [
                'critico' => $statsCritico,
                'alerta'  => $statsAlerta,
                'regla'   => $statsRegla,
            ],
            'eventsByDate' => $eventsByDate,
            'list' => $events,
        ]);
    }

    private function severity(int $daysLeft): string
    {
        if ($daysLeft <= 30) return 'danger'; // incluye vencidos
        if ($daysLeft <= 60) return 'warning';
        return 'success';
    }
}
