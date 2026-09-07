<?php

namespace App\Services\Graph;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class GraphAccessTokenProvider
{
    private const CACHE_KEY = 'graph_api_access_token';

    /**
     * Margen de seguridad (segundos) para renovar el token antes de que expire realmente.
     */
    private const EXPIRY_LEEWAY = 60;

    public function __construct(private Client $client) {}

    /**
     * Devuelve un access token válido, reutilizando el cacheado si aún no expira.
     */
    public function getToken(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        [$token, $expiresIn] = $this->requestToken();

        Cache::put(self::CACHE_KEY, $token, max(self::EXPIRY_LEEWAY, $expiresIn - self::EXPIRY_LEEWAY));

        return $token;
    }

    /**
     * @return array{0: string, 1: int} [access_token, expires_in]
     */
    private function requestToken(): array
    {
        $tenantId = config('graph.tenant_id');

        try {
            $response = $this->client->post(
                "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
                [
                    'form_params' => [
                        'client_id' => config('graph.client_id'),
                        'client_secret' => config('graph.client_secret'),
                        'scope' => 'https://graph.microsoft.com/.default',
                        'grant_type' => 'client_credentials',
                    ],
                ]
            );
        } catch (RequestException $e) {
            throw new RuntimeException(
                'No se pudo obtener el token de acceso de Microsoft Graph: '.$this->describeAadError($e)
            );
        } catch (Throwable) {
            // No se incluye el mensaje original: podría contener detalles de la petición
            // (incluido el cuerpo con el client_secret) que no deben quedar en logs.
            throw new RuntimeException('No se pudo obtener el token de acceso de Microsoft Graph (error de red).');
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data) || empty($data['access_token'])) {
            throw new RuntimeException('La respuesta del endpoint de token de Microsoft Graph no incluyó un access_token.');
        }

        return [$data['access_token'], (int) ($data['expires_in'] ?? 3600)];
    }

    /**
     * Extrae únicamente el código/descripción de error que Azure AD devuelve en el body,
     * sin exponer el request original (que contiene el client_secret).
     */
    private function describeAadError(RequestException $e): string
    {
        $response = $e->getResponse();

        if (! $response) {
            return 'sin respuesta del servidor (fallo de red o timeout).';
        }

        $body = json_decode((string) $response->getBody(), true);
        $code = $body['error'] ?? null;
        $description = $body['error_description'] ?? null;

        if ($code) {
            $shortDescription = $description ? strtok((string) $description, "\r\n") : null;

            return "HTTP {$response->getStatusCode()} ({$code})".($shortDescription ? " - {$shortDescription}" : '');
        }

        return "HTTP {$response->getStatusCode()}";
    }
}
