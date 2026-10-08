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

        return Http::response(['error' => 'unhandled: '.$method.' '.$url], 404);
    });
}

test('el explorador carga bien la página (solo lectura)', function () {
    fakeArbolExplorador();

    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador'))
        ->assertOk()
        ->assertSee('sharepointExplorador', false);
});

test('cualquier usuario con cuenta puede entrar al explorador (es solo visualización)', function () {
    fakeArbolExplorador();

    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('sharepoint.explorador'))
        ->assertOk();
});

test('un invitado sin sesión no puede entrar al explorador', function () {
    $this->get(route('sharepoint.explorador'))
        ->assertRedirect(route('login'));
});

test('el endpoint de contenido regresa carpetas y archivos, carpetas primero y alfabético', function () {
    fakeArbolExplorador();

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(2)
        ->and($response['items'][0]['name'])->toBe('Formatos')
        ->and($response['items'][0]['type'])->toBe('folder')
        ->and($response['items'][0]['has_children'])->toBeTrue()
        ->and($response['items'][1]['name'])->toBe('Política de prueba.pdf')
        ->and($response['items'][1]['type'])->toBe('file')
        ->and($response['items'][1]['web_url'])->toBe('https://sharepoint.example/politica.pdf')
        ->and($response['items'][1]['size'])->toBe(2048);
});

test('el endpoint de contenido requiere folder_id', function () {
    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido'))
        ->assertSessionHasErrors('folder_id');
});

test('un nombre de archivo con comillas y & llega intacto en la respuesta JSON (no es HTML embebido)', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), '/items/id-area-calidad/children')) {
            return Http::response(['value' => [
                ['id' => 'id-archivo-raro', 'name' => 'I+D & "Calidad".pdf', 'size' => 100, 'file' => ['mimeType' => 'application/pdf'], 'webUrl' => 'https://sharepoint.example/raro.pdf'],
            ]], 200);
        }

        return Http::response(['value' => []], 200);
    });

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['items'][0]['name'])->toBe('I+D & "Calidad".pdf');
});
