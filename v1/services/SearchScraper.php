<?php
/**
 * Scraper para búsquedas de libros y paginación en isbndb.com
 */

declare(strict_types=1);

namespace ApiV1\Services;

require_once __DIR__ . '/HttpClient.php';

use DOMDocument;
use DOMXPath;
use DOMNode;
use RuntimeException;

class SearchScraper
{
    private HttpClient $http;

    public function __construct(?HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    /**
     * Realiza la búsqueda de libros y retorna lista paginada
     */
    public function search(string $query, int $page = 1): array
    {
        $trimmedQuery = trim($query);
        if ($trimmedQuery === '') {
            throw new RuntimeException('El término de búsqueda no puede estar vacío.');
        }

        $page = max(1, $page);

        // Construcción de la URL de destino
        if ($page === 1) {
            $targetUrl = 'https://isbndb.com/search/books/?search_param=books&x=' . urlencode($trimmedQuery);
        } else {
            $targetUrl = 'https://isbndb.com/books/more?query=' . urlencode($trimmedQuery) . '&column=&edition=&year=&language=&page=' . $page;
        }

        $response = $this->http->get($targetUrl, 'https://isbndb.com/');
        $body = $response['body'];
        $effectiveUrl = $response['effectiveUrl'];

        // Si isbndb redirigió directamente a una ficha de libro (/book/...)
        if (preg_match('#/book/([0-9Xx]+)#', $effectiveUrl, $matches)) {
            $directIsbn = $matches[1];
            return [
                'items' => [
                    [
                        'isbn13'      => $directIsbn,
                        'title'       => $trimmedQuery,
                        'authors'     => [],
                        'publisher'   => null,
                        'publish_date'=> null,
                        'binding'     => null,
                        'cover_image' => "https://images.isbndb.com/covers/" . substr($directIsbn, -4, 2) . "/" . substr($directIsbn, -2) . "/{$directIsbn}.jpg",
                        'detail_url'  => "/v1/book.php?isbn={$directIsbn}"
                    ]
                ],
                'pagination' => [
                    'current_page'  => 1,
                    'has_more'      => false,
                    'next_page_url' => null
                ]
            ];
        }

        $items = $this->parseSearchResults($body);
        $hasMore = count($items) > 0;

        return [
            'items' => $items,
            'pagination' => [
                'current_page'  => $page,
                'has_more'      => $hasMore,
                'next_page_url' => $hasMore ? "/v1/search.php?query=" . urlencode($trimmedQuery) . "&page=" . ($page + 1) : null
            ]
        ];
    }

    /**
     * Parsea el HTML de los resultados de búsqueda
     */
    private function parseSearchResults(string $html): array
    {
        if (empty(trim($html))) {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $items = [];

        // Buscar contenedores de libros o enlaces a /book/
        // isbndb suele usar contenedores con enlaces a /book/{isbn}
        $bookLinks = $xpath->query("//a[contains(@href, '/book/')]");
        $seenIsbns = [];

        if ($bookLinks && $bookLinks->length > 0) {
            foreach ($bookLinks as $linkNode) {
                $href = $linkNode->getAttribute('href');
                if (preg_match('#/book/([0-9Xx]+)#', $href, $matches)) {
                    $isbn = $matches[1];
                    if (isset($seenIsbns[$isbn])) {
                        continue;
                    }
                    $seenIsbns[$isbn] = true;

                    // Buscar el contenedor padre más cercano (fila / tarjeta / artículo)
                    $container = $linkNode;
                    for ($i = 0; $i < 4; $i++) {
                        if ($container->parentNode instanceof DOMNode && $container->parentNode->nodeName !== 'body') {
                            $container = $container->parentNode;
                        }
                    }

                    // Título
                    $title = trim($linkNode->textContent);
                    if (empty($title) || strlen($title) < 2) {
                        $titleNodes = $xpath->query(".//h2 | .//h3 | .//h4 | .//strong | .//a[contains(@href, '/book/')]", $container);
                        if ($titleNodes && $titleNodes->length > 0) {
                            $title = trim($titleNodes->item(0)->textContent);
                        }
                    }

                    // Imagen
                    $coverImage = null;
                    $imgNodes = $xpath->query(".//img[contains(@src, 'covers') or contains(@src, 'isbndb')]/@src", $container);
                    if ($imgNodes && $imgNodes->length > 0) {
                        $coverImage = $imgNodes->item(0)->nodeValue;
                    }

                    // Autores y metadatos complementarios en el texto del contenedor
                    $containerText = $container->textContent;
                    $authors = [];
                    if (preg_match('/(?:by|autor|authors?:?)\s+([^,\n\r\|]+)/i', $containerText, $authorMatches)) {
                        $authors[] = trim($authorMatches[1]);
                    }

                    $items[] = [
                        'isbn13'       => $isbn,
                        'title'        => $title ?: "Libro ISBN {$isbn}",
                        'authors'      => $authors,
                        'publisher'    => null,
                        'publish_date' => null,
                        'binding'      => null,
                        'cover_image'  => $coverImage,
                        'detail_url'   => "/v1/book.php?isbn={$isbn}"
                    ];
                }
            }
        }

        return $items;
    }
}

