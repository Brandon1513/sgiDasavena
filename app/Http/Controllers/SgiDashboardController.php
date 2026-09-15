<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Solicitudes por estado
        $porEstado = (clone $baseQuery)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Solicitudes por día (últimos 30 días)
        $porDia = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

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

        // Documentos por vencer (vigentes, en zona de alerta/crítico/vencido)
        $docQuery = Documento::query()->where('estatus', 'vigente')->with('versionVigente');
        if (!$user->hasRole('administrador') && !$user->hasRole('administrador_sgi') && !empty($user->area)) {
            $docQuery->where('area', $user->area);
        }
        $documentosUrgentes = $docQuery->get()
            ->filter(fn ($d) => in_array($d->semaforo_vencimiento, ['vencido', 'critico', 'alerta']))
            ->sortBy('dias_para_vencimiento')
            ->values();

        $totalPorVencer     = $documentosUrgentes->count();
        $documentosPorVencer = $documentosUrgentes->take(6);

        // ── SLA / tiempos de respuesta ──
        // Promedio de horas desde que se crea la solicitud hasta que el jefe la aprueba.
        $horasAprobacion = (clone $baseQuery)
            ->whereNotNull('aprobado_jefe_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, aprobado_jefe_at)) as promedio')
            ->value('promedio');

        // Promedio de horas desde la aprobación del jefe hasta que SGI atiende la solicitud.
        $horasAtencion = (clone $baseQuery)
            ->whereNotNull('aprobado_jefe_at')
            ->whereNotNull('atendido_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, aprobado_jefe_at, atendido_at)) as promedio')
            ->value('promedio');

        // Solicitudes pendientes que llevan más de 3 días naturales sin moverse (fuera de SLA).
        $vencidasSla = (clone $baseQuery)
            ->where('estado', 'pendiente')
            ->where('created_at', '<=', now()->subDays(3))
            ->count();

        // ── Desglose por área ──
        // El área "real" de una solicitud es la del documento al que se refiere (actualización/baja);
        // solo cuando no hay documento asociado (alta de documento nuevo) se usa el área de quien solicita.
        $areasConDatos = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'solicitudes_formatos.user_id')
            ->leftJoin('documentos', 'documentos.id', '=', 'solicitudes_formatos.documento_id')
            ->selectRaw("
                COALESCE(documentos.area, users.area, 'Sin área') as area,
                COUNT(*) as total,
                SUM(CASE WHEN solicitudes_formatos.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN solicitudes_formatos.estado = 'atendido' THEN 1 ELSE 0 END) as atendidas
            ")
            ->groupBy('area')
            ->get()
            ->keyBy('area');

        // Administración y SGI ven TODOS los departamentos que existen, aunque aún no tengan
        // ninguna solicitud. No hay catálogo de áreas: se toma la unión de las distintas en
        // `documentos.area` (la lista más confiable) y `users.area` (cubre áreas sin documentos aún).
        if ($user->hasRole('administrador') || $user->hasRole('administrador_sgi')) {
            $areasDocumentos = Documento::query()
                ->whereNotNull('area')->where('area', '!=', '')
                ->distinct()->pluck('area');

            $areasUsuarios = User::query()
                ->whereNotNull('area')->where('area', '!=', '')
                ->distinct()->pluck('area');

            $todasLasAreas = $areasDocumentos->concat($areasUsuarios)->unique()->sort()->values();
        } else {
            $todasLasAreas = $areasConDatos->keys();
        }

        $porArea = $todasLasAreas
            ->map(function ($area) use ($areasConDatos) {
                $fila = $areasConDatos->get($area);

                return (object) [
                    'area' => $area,
                    'total' => $fila->total ?? 0,
                    'pendientes' => $fila->pendientes ?? 0,
                    'atendidas' => $fila->atendidas ?? 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return view('dashboard', [
            'total'             => $total,
            'pendientes'        => $pendientes,
            'aprobadoJefe'      => $aprobadoJefe,
            'atendidas'         => $atendidas,
            'rechazadas'        => $rechazadas,
            'ultimasSolicitudes' => $ultimasSolicitudes,
            'porEstado'         => $porEstado,
            'porDia'            => $porDia,
            'totalPorVencer'      => $totalPorVencer,
            'documentosPorVencer' => $documentosPorVencer,
            'horasAprobacion'   => $horasAprobacion !== null ? round($horasAprobacion, 1) : null,
            'horasAtencion'     => $horasAtencion !== null ? round($horasAtencion, 1) : null,
            'vencidasSla'       => $vencidasSla,
            'porArea'           => $porArea,
        ]);
    }
}
