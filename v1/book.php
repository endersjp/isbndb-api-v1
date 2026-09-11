<?php
/**
 * Endpoint REST: Metadatos y ficha técnica completa de un libro
 * GET /v1/book.php?isbn={isbn}
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

require_once __DIR__ . '/services/BookScraper.php';

use ApiV1\Services\BookScraper;

$isbn = isset($_GET['isbn']) ? trim((string) $_GET['isbn']) : '';

if ($isbn === '') {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'code'    => 400,
        'message' => 'El parámetro "isbn" es requerido.',
        'data'    => null
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $scraper = new BookScraper();
    $bookData = $scraper->getBookByIsbn($isbn);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'code'   => 200,
        'data'   => $bookData
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

