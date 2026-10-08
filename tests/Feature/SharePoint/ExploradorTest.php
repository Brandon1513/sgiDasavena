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
                ['id' => 'id-sgi', 'name' => 'SGI', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-sgi/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-obsoleto-root', 'name' => 'Sistema de Gestión Obsoleto', 'folder' => ['childCount' => 1]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-obsoleto-root/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-obsoleto-calidad', 'name' => 'Calidad', 'folder' => ['childCount' => 0]],
            ]], 200);
        }

        if (str_contains($url, '/items/id-area-calidad/children') && $method === 'GET') {
            return Http::response(['value' => [
                ['id' => 'id-formatos', 'name' => 'Formatos', 'folder' => ['childCount' => 1]],
                ['id' => 'id-archivo', 'name' => 'Política de prueba.pdf', 'size' => 2048, 'file' => ['mimeType' => 'application/pdf'], 'webUrl' => 'https://sharepoint.example/politica.pdf'],
            ]], 200);
        }

        return Http::response(['error' => 'unhandled: ' . $method . ' ' . $url], 404);
    });
}

test('el explorador muestra los departamentos como carpetas de la raíz', function () {
    fakeArbolExplorador();

    $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador'))
        ->assertOk()
        ->assertSee('Calidad');
});

test('un usuario sin rol administrador_sgi no puede entrar al explorador', function () {
    $usuario = User::factory()->create();
    $usuario->assignRole('usuario');

    $this->actingAs($usuario)
        ->get(route('sharepoint.explorador'))
        ->assertForbidden();
});

test('el endpoint de contenido regresa carpetas y archivos de una carpeta dada', function () {
    fakeArbolExplorador();

    $response = $this->actingAs($this->adminSgi)
        ->get(route('sharepoint.explorador.contenido', ['folder_id' => 'id-area-calidad']))
        ->assertOk()
        ->json();

    expect($response['items'])->toHaveCount(2);

    $folder = collect($response['items'])->firstWhere('name', 'Formatos');
    $file = collect($response['items'])->firstWhere('name', 'Política de prueba.pdf');

    expect($folder['type'])->toBe('folder')
        ->and($file['type'])->toBe('file')
        ->and($file['web_url'])->toBe('https://sharepoint.example/politica.pdf')
        ->and($file['size'])->toBe(2048);
});
