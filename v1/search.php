<?php
/**
 * Endpoint REST: Búsqueda y paginación de libros
 * GET /v1/search.php?query={termino}&page={n}
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/services/SearchScraper.php';

use ApiV1\Services\SearchScraper;

$query = isset($_GET['query']) ? trim((string) $_GET['query']) : '';
$page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($query === '') {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'code'    => 400,
        'message' => 'El parámetro "query" es requerido para realizar la búsqueda.',
        'data'    => null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $scraper = new SearchScraper();
    $result = $scraper->search($query, $page);

    http_response_code(200);
    echo json_encode([
        'status'     => 'success',
        'code'       => 200,
        'data'       => $result['items'],
        'pagination' => $result['pagination']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        'status'  => 'error',
        'code'    => $code,
        'message' => $e->getMessage(),
        'data'    => null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

