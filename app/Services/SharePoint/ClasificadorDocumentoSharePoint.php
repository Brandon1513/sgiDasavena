<?php

namespace App\Services\SharePoint;

use Illuminate\Support\Str;

/**
 * Traduce los valores reales (y a veces sucios: typos, variantes "Anexo X")
 * de tipo_documento/area a la estructura real ya existente en SharePoint.
 * Cuando no hay un match confiable, regresa null a propósito: es preferible
 * no sugerir nada a arriesgarse a archivar un documento en el lugar
 * equivocado o crear una carpeta duplicada.
 */
class ClasificadorDocumentoSharePoint
{
    /**
     * Palabra clave (ya sin acentos, en minúsculas) => tipo canónico.
     */
    private const PALABRAS_CLAVE_TIPO = [
        'procedimiento' => 'Procedimiento',
        'formato' => 'Formato',
        'manual' => 'Manual',
        'instructivo' => 'Instructivo',
        'polit' => 'Política',
    ];

    /**
     * Tipo canónico => nombre real de la carpeta de vigentes (ya existe tal
     * cual en SharePoint, bajo la carpeta raíz).
     */
    private const CARPETA_VIGENTE = [
        'Procedimiento' => 'Procedimientos',
        'Formato' => 'Formatos',
        'Manual' => 'Manuales',
        'Instructivo' => 'Instructivos',
        'Política' => 'Políticas',
    ];

    public static function normalizar(string $valor): string
    {
        return Str::lower(Str::ascii(trim($valor)));
    }

    /**
     * Clasifica un tipo_documento libre (con typos o variantes "Anexo X")
     * a uno de los 5 tipos canónicos, o null si no hay match confiable.
     */
    public static function normalizarTipo(?string $tipoDocumento): ?string
    {
        if (!$tipoDocumento) {
            return null;
        }

        $valor = self::normalizar($tipoDocumento);

        foreach (self::PALABRAS_CLAVE_TIPO as $clave => $canonico) {
            if (str_contains($valor, $clave)) {
                return $canonico;
            }
        }

        return null;
    }

    /**
     * Nombre real de la carpeta de vigentes para un tipo canónico.
     */
    public static function carpetaVigente(string $tipoCanonico): ?string
    {
        return self::CARPETA_VIGENTE[$tipoCanonico] ?? null;
    }

    /**
     * Nombre a usar si hay que CREAR una carpeta de obsoletos nueva (no
     * existía ninguna ya para esa combinación Área+Tipo). Sigue el patrón
     * más común observado ("Formatos obsoletos", "Procedimientos
     * obsoletos", etc.).
     */
    public static function nombreObsoletosFallback(string $tipoCanonico): ?string
    {
        $plural = self::carpetaVigente($tipoCanonico);

        return $plural ? "{$plural} obsoletos" : null;
    }

    /**
     * Busca, entre los nombres de carpetas ya existentes (p. ej. hijos de
     * una carpeta de Área dentro de "Sistema de Gestión Obsoleto"), una que
     * corresponda al tipo canónico — aceptando variantes singular/plural ya
     * reales como "Manual obsoletos" en vez de "Manuales obsoletos".
     * Regresa el nombre EXACTO encontrado (tal cual está en SharePoint) o
     * null si ninguna corresponde.
     */
    public static function buscarNombreObsoletosExistente(array $nombresCarpetas, string $tipoCanonico): ?string
    {
        return self::buscarPorTipo($nombresCarpetas, $tipoCanonico, requiereObsoleto: true);
    }

    /**
     * Igual que buscarNombreObsoletosExistente, pero para la carpeta de
     * VIGENTES de un tipo dentro de una carpeta de Área (p. ej. hijos de
     * "Calidad"): acepta "Formato"/"Formatos", "Manual"/"Manuales", etc.
     */
    public static function buscarNombreTipoExistente(array $nombresCarpetas, string $tipoCanonico): ?string
    {
        return self::buscarPorTipo($nombresCarpetas, $tipoCanonico, requiereObsoleto: false);
    }

    private static function buscarPorTipo(array $nombresCarpetas, string $tipoCanonico, bool $requiereObsoleto): ?string
    {
        $singular = self::normalizar($tipoCanonico);
        $plural = self::normalizar(self::carpetaVigente($tipoCanonico) ?? $tipoCanonico);

        foreach ($nombresCarpetas as $nombre) {
            $normalizado = self::normalizar($nombre);

            if ($requiereObsoleto && !str_contains($normalizado, 'obsolet')) {
                continue;
            }

            if (!$requiereObsoleto && str_contains($normalizado, 'obsolet')) {
                continue;
            }

            if (str_starts_with($normalizado, $singular) || str_starts_with($normalizado, $plural)) {
                return $nombre;
            }
        }

        return null;
    }

    /**
     * Busca, entre los nombres de carpetas de Área ya existentes (hijos de
     * "Sistema de Gestión Obsoleto"), la que corresponde a documento.area,
     * ignorando mayúsculas/acentos/espacios. Regresa el nombre EXACTO
     * encontrado o null si no hay match confiable.
     */
    public static function buscarNombreAreaExistente(array $nombresCarpetas, ?string $area): ?string
    {
        if (!$area) {
            return null;
        }

        $buscado = self::normalizar($area);

        foreach ($nombresCarpetas as $nombre) {
            if (self::normalizar($nombre) === $buscado) {
                return $nombre;
            }
        }

        return null;
    }
}
