<?php

use App\Services\SharePoint\ClasificadorDocumentoSharePoint as Clasificador;

test('normaliza tipos de documento reales, incluyendo variantes y typos', function (string $valor, ?string $esperado) {
    expect(Clasificador::normalizarTipo($valor))->toBe($esperado);
})->with([
    ['Procedimiento', 'Procedimiento'],
    ['Anexo Procedimiento', 'Procedimiento'],
    ['Formato', 'Formato'],
    ['Formato Anexo', 'Formato'],
    ['Anexo Formato', 'Formato'],
    ['Manual', 'Manual'],
    ['Anexo Manual', 'Manual'],
    ['Manualll', 'Manual'],
    ['Instructivo', 'Instructivo'],
    ['Instructivog', 'Instructivo'],
    ['Política', 'Política'],
    ['Política Anexo', 'Política'],
    ['Anexo', null],
    ['', null],
]);

test('null cuando no hay tipo_documento', function () {
    expect(Clasificador::normalizarTipo(null))->toBeNull();
});

test('carpetaVigente mapea al nombre real ya existente en SharePoint', function () {
    expect(Clasificador::carpetaVigente('Procedimiento'))->toBe('Procedimientos')
        ->and(Clasificador::carpetaVigente('Formato'))->toBe('Formatos')
        ->and(Clasificador::carpetaVigente('Manual'))->toBe('Manuales')
        ->and(Clasificador::carpetaVigente('Instructivo'))->toBe('Instructivos')
        ->and(Clasificador::carpetaVigente('Política'))->toBe('Políticas');
});

test('nombreObsoletosFallback sigue el patrón "{Tipo plural} obsoletos"', function () {
    expect(Clasificador::nombreObsoletosFallback('Formato'))->toBe('Formatos obsoletos')
        ->and(Clasificador::nombreObsoletosFallback('Manual'))->toBe('Manuales obsoletos');
});

test('buscarNombreObsoletosExistente respeta una carpeta ya real aunque esté en singular', function () {
    $carpetas = ['Formatos obsoletos', 'Instructivos obsoletos', 'Manual obsoletos', 'Políticas obsoletos', 'Procedimientos obsoletos'];

    expect(Clasificador::buscarNombreObsoletosExistente($carpetas, 'Manual'))->toBe('Manual obsoletos')
        ->and(Clasificador::buscarNombreObsoletosExistente($carpetas, 'Formato'))->toBe('Formatos obsoletos');
});

test('buscarNombreObsoletosExistente regresa null si la combinación nunca existió', function () {
    $carpetas = ['Formatos obsoletos', 'Procedimientos obsoletos'];

    expect(Clasificador::buscarNombreObsoletosExistente($carpetas, 'Manual'))->toBeNull();
});

test('buscarNombreAreaExistente ignora mayúsculas, acentos y espacios', function () {
    $carpetas = ['Administración', 'Almacén', 'Higiene y Seguridad industrial', 'Producción'];

    expect(Clasificador::buscarNombreAreaExistente($carpetas, 'producción'))->toBe('Producción')
        ->and(Clasificador::buscarNombreAreaExistente($carpetas, '  ALMACÉN  '))->toBe('Almacén')
        ->and(Clasificador::buscarNombreAreaExistente($carpetas, 'Higiene y seguridad Industrial'))->toBe('Higiene y Seguridad industrial');
});

test('buscarNombreAreaExistente regresa null para valores sin match confiable', function () {
    $carpetas = ['Administración', 'Almacén', 'Calidad'];

    expect(Clasificador::buscarNombreAreaExistente($carpetas, 'Gerencia de Talento y Cultura'))->toBeNull()
        ->and(Clasificador::buscarNombreAreaExistente($carpetas, null))->toBeNull();
});
