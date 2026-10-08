<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesSeeder::class);

    Cache::put('graph_api_access_token', 'token-de-prueba', 3600);

    $this->adminSgi = User::factory()->create();
    $this->adminSgi->assignRole('administrador_sgi');
});

function fakeArbolExplorador(): void
{
    Http::fake(function ($request) {
        $url = $request->url();
        $method = $request->method();

        if (preg_match('#/items/root/children#', $url) && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-raiz-inocuidad', 'name' => 'Sistema de Gestión de Inocuidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-raiz-inocuidad/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-area-calidad', 'name' => 'Calidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-area-calidad/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-formatos', 'name' => 'Formatos', 'folder' => ['childCount' => 1]],
                ['id' => 'id-archivo', 'name' => 'Política de prueba.pdf', 'size' => 2048, 'file' => ['mimeType' => 'application/pdf'], 'webUrl' => 'https://sharepoint.example/politica.pdf'],
            ]], 200);
        }

        if (str_contains($url, '/items/id-formatos/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-solo-carpeta', 'name' => 'F-CAL-01', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-solo-carpeta/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-archivo-final', 'name' => 'F-CAL-01 Rev.02.pdf', 'size' => 1024, 'file' => ['mimeType' => 'application/pdf'], 'webUrl' => 'https://sharepoint.example/f-cal-01.pdf'],
            ]], 200);
        }

        return Http::response(['error' => 'unhandled: ' . $method . ' ' . $url], 404);
    });
}

test('el explorador muestra los departamentos como carpetas de la raíz, sin distinguir vigentes/obsoletos', function () {
    fakeArbolExplorador();

    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador'))
        ->assertOk()
        ->assertSee('Calidad')
        ->assertDontSee('Obsoletos');
});

test('cualquier usuario con cuenta puede entrar al explorador (es solo visualización)', function () {
    fakeArbolExplorador();

    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('sharepoint.explorador'))
        ->assertOk()
        ->assertSee('Calidad');
});

test('un invitado sin sesión no puede entrar al explorador', function () {
    $this->get(route('sharepoint.explorador'))
        ->assertRedirect(route('login'));
});

test('el endpoint de contenido pide webUrl y size a Graph (antes se perdían por el $select)', function () {
    fakeArbolExplorador();

    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']));

    Http::assertSent(fn ($request) => str_contains($request->url(), 'webUrl') && str_contains($request->url(), 'size'));
});

test('el endpoint de contenido regresa carpetas y archivos ordenados (carpetas primero, alfabético)', function () {
    fakeArbolExplorador();

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(2)
        ->and($response['items'][0]['name'])->toBe('Formatos')
        ->and($response['items'][0]['type'])->toBe('folder')
        ->and($response['items'][1]['name'])->toBe('Política de prueba.pdf')
        ->and($response['items'][1]['type'])->toBe('file')
        ->and($response['items'][1]['web_url'])->toBe('https://sharepoint.example/politica.pdf')
        ->and($response['items'][1]['size'])->toBe(2048)
        ->and($response['items'][1]['extension'])->toBe('pdf');
});

test('un nombre de departamento con comillas no rompe el x-data embebido (regresión)', function () {
    Http::fake(function ($request) {
        if (preg_match('#/items/root/children#', $request->url())) {
            return Http::response(['value' => [
                ['id' => 'id-raiz-inocuidad', 'name' => 'Sistema de Gestión de Inocuidad', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($request->url(), '/items/id-raiz-inocuidad/children')) {
            return Http::response(['value' => [
                ['id' => 'id-area-rara', 'name' => 'I+D & "Calidad"', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        return Http::response(['value' => []], 200);
    });

    $html = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador'))
        ->assertOk()
        ->getContent();

    // El bloque x-data no debe contener la comilla ni el & crudos: romperían
    // el atributo HTML y la expresión que evalúa Alpine (ver JSON_HEX_*).
    preg_match('/x-data="(sharepointExplorador\(.*?\))"\s/s', $html, $match);

    expect($match)->not->toBeEmpty()
        ->and($match[1])->not->toContain('"Calidad"')
        ->and($match[1])->not->toContain(' & ');
});
