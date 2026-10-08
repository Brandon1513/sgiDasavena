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

test('el explorador carga bien la página (solo lectura, solo carpetas)', function () {
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

test('el endpoint de contenido regresa solo carpetas (sin archivos), ordenadas alfabéticamente', function () {
    fakeArbolExplorador();

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['carpetas'])->toHaveCount(1)
        ->and($response['carpetas'][0]['id'])->toBe('id-formatos')
        ->and($response['carpetas'][0]['name'])->toBe('Formatos')
        ->and($response['carpetas'][0]['has_children'])->toBeTrue();
});

test('el endpoint de contenido requiere folder_id', function () {
    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido'))
        ->assertSessionHasErrors('folder_id');
});

test('un nombre de carpeta con comillas y & llega intacto en la respuesta JSON (no es HTML embebido)', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), '/items/id-area-calidad/children')) {
            return Http::response(['value' => [
                ['id' => 'id-area-rara', 'name' => 'I+D & "Calidad"', 'folder' => ['childCount' => 0]],
            ]], 200);
        }

        return Http::response(['value' => []], 200);
    });

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['carpetas'][0]['name'])->toBe('I+D & "Calidad"');
});
