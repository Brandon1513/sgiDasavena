<?php

namespace App\Domains\Incidencias\Enums;

enum EstadoAccionCorrectiva: string
{
    case BORRADOR = 'borrador';
    case ABIERTA = 'abierta';
    case CONTENCION = 'contencion';
    case ANALISIS = 'analisis';
    case VALIDACION_CAUSA = 'validacion_causa';
    case PLAN_ACCION = 'plan_accion';
    case EJECUCION = 'ejecucion';
    case VERIFICACION_CIERRE = 'verificacion_cierre';
    case ESPERA_EFICACIA = 'espera_eficacia';
    case VERIFICACION_EFICACIA = 'verificacion_eficacia';
    case CERRADA = 'cerrada';
}
