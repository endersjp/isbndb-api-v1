<?php
/**
 * Cliente HTTP basado en cURL para realizar peticiones con cabeceras de navegación
 */

declare(strict_types=1);

namespace ApiV1\Services;

require_once __DIR__ . '/../config/http.php';

use ApiV1\Config\HttpConfig;
use RuntimeException;

class HttpClient
{
    /**
     * Realiza una petición GET y retorna un array con ['body' => string, 'statusCode' => int, 'effectiveUrl' => string]
     */
    public function get(string $url, ?string $referer = null): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('No se pudo inicializar cURL.');
        }

        $options = HttpConfig::getDefaultCurlOptions($referer ?? HttpConfig::BASE_URL);
        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        $errorNo = curl_errno($ch);
        $errorMsg = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        curl_close($ch);

        if ($errorNo !== 0) {
            throw new RuntimeException("Error en petición cURL: {$errorMsg} (Código {$errorNo})");
        }

        return [
            'body'         => is_string($body) ? $body : '',
            'statusCode'   => $statusCode,
            'effectiveUrl' => $effectiveUrl
        ];
    }
}

