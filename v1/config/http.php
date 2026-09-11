<?php
/**
 * Configuración HTTP y cabeceras anti-bloqueo para cURL
 */

declare(strict_types=1);

namespace ApiV1\Config;

class HttpConfig
{
    public const BASE_URL = 'https://isbndb.com';
    public const TIMEOUT = 15;
    public const CONNECT_TIMEOUT = 10;

    /**
     * Retorna las opciones predeterminadas para solicitudes cURL hacia isbndb.com
     */
    public static function getDefaultCurlOptions(string $referer = 'https://isbndb.com/'): array
    {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'accept-language: es-ES,es;q=0.9,en;q=0.8',
                'priority: u=0, i',
                'referer: ' . $referer,
                'sec-ch-ua: "Chromium";v="124", "Google Chrome";v="124"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: document',
                'sec-fetch-mode: navigate',
                'sec-fetch-site: same-origin',
                'sec-fetch-user: ?1',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ]
        ];
    }
}

