<?php
/**
 * Scraper para obtener la ficha técnica y metadatos de un libro
 */

declare(strict_types=1);

namespace ApiV1\Services;

require_once __DIR__ . '/HttpClient.php';

use DOMDocument;
use DOMXPath;
use DOMNodeList;
use RuntimeException;

class BookScraper
{
    private HttpClient $http;

    public function __construct(?HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    /**
     * Extrae los metadatos completos de un libro a partir de su ISBN
     */
    public function getBookByIsbn(string $isbn): array
    {
        $cleanIsbn = preg_replace('/[^0-9Xx]/', '', $isbn);
        if (empty($cleanIsbn)) {
            throw new RuntimeException('El ISBN proporcionado no tiene un formato válido.');
        }

        $url = "https://isbndb.com/book/{$cleanIsbn}";
        $response = $this->http->get($url, 'https://isbndb.com/');

        if ($response['statusCode'] === 404 || empty($response['body'])) {
            throw new RuntimeException("No se encontró ningún libro con el ISBN: {$cleanIsbn}", 404);
        }

        return $this->parseHtml($response['body'], $cleanIsbn);
    }

    /**
     * Parsea el HTML de la página de detalle del libro
     */
    public function parseHtml(string $html, string $fallbackIsbn): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Extractor de texto por encabezado <th>
        $getText = function (string $label) use ($xpath): ?string {
            $query = sprintf("//tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '%s')]]/td", strtolower($label));
            $nodes = $xpath->query($query);
            if ($nodes && $nodes->length > 0) {
                $text = trim($nodes->item(0)->textContent);
                return $text !== '' ? $text : null;
            }
            return null;
        };

        // Título
        $title = $getText('full title') ?? $getText('title');
        if (!$title) {
            $h1Nodes = $xpath->query("//h1[contains(@class, 'book-title')] | //h1");
            if ($h1Nodes && $h1Nodes->length > 0) {
                $title = trim($h1Nodes->item(0)->textContent);
            }
        }

        // ISBNs
        $isbn13 = $getText('isbn13') ?? $getText('isbn-13') ?? $fallbackIsbn;
        $isbn10 = $getText('isbn10') ?? $getText('isbn-10');

        // Autores
        $authors = [];
        $authorQuery = "//tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'author')]]/td//a";
        $authorNodes = $xpath->query($authorQuery);
        if ($authorNodes && $authorNodes->length > 0) {
            foreach ($authorNodes as $aNode) {
                $name = trim($aNode->textContent);
                if ($name !== '') {
                    $authors[] = [
                        'name' => $name,
                        'path' => $aNode->getAttribute('href') ?: null
                    ];
                }
            }
        } elseif ($rawAuthor = $getText('authors') ?? $getText('author')) {
            $authors[] = ['name' => $rawAuthor, 'path' => null];
        }

        // Editorial / Publisher
        $publisherName = null;
        $publisherPath = null;
        $pubQuery = "//tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'publisher')]]/td";
        $pubNodes = $xpath->query($pubQuery);
        if ($pubNodes && $pubNodes->length > 0) {
            $td = $pubNodes->item(0);
            $publisherName = trim($td->textContent);
            $aInside = $xpath->query(".//a", $td);
            if ($aInside && $aInside->length > 0) {
                $publisherPath = $aInside->item(0)->getAttribute('href');
            }
        }

        // Sinopsis
        $synopsis = null;
        $synNodes = $xpath->query("//tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'synopsis')]]/td");
        if ($synNodes && $synNodes->length > 0) {
            $synopsis = trim(strip_tags($dom->saveHTML($synNodes->item(0))));
            // Limpiar saltos de línea repetidos
            $synopsis = preg_replace('/\s+/', ' ', $synopsis);
        }

        // Páginas
        $pagesRaw = $getText('pages');
        $pages = $pagesRaw !== null ? (int) preg_replace('/[^0-9]/', '', $pagesRaw) : null;

        // Materias / Subjects
        $subjects = [];
        $subQuery = "//tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'subject')]]/td//ul/li/a | //tr[th[contains(translate(normalize-space(text()), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'subject')]]/td//a";
        $subNodes = $xpath->query($subQuery);
        if ($subNodes && $subNodes->length > 0) {
            foreach ($subNodes as $sNode) {
                $sName = trim($sNode->textContent);
                if ($sName !== '') {
                    $subjects[] = [
                        'name' => $sName,
                        'path' => $sNode->getAttribute('href') ?: null
                    ];
                }
            }
        }

        // Imagen de portada
        $coverImage = null;
        $imgNodes = $xpath->query("//div[contains(@class, 'book-image')]//img/@src | //img[contains(@src, 'covers')]/@src | //img[contains(@class, 'cover')]/@src");
        if ($imgNodes && $imgNodes->length > 0) {
            $coverImage = $imgNodes->item(0)->nodeValue;
        }

        // Formateo de idioma a código o nombre limpio
        $language = $getText('language');

        return [
            'title'        => $title,
            'isbn13'       => $isbn13,
            'isbn10'       => $isbn10,
            'cover_image'  => $coverImage,
            'authors'      => $authors,
            'publisher'    => $publisherName ? [
                'name' => $publisherName,
                'path' => $publisherPath
            ] : null,
            'publish_date' => $getText('publish date'),
            'binding'      => $getText('binding'),
            'pages'        => $pages,
            'synopsis'     => $synopsis,
            'language'     => $language,
            'dimensions'   => $getText('dimensions'),
            'weight'       => $getText('weight'),
            'subjects'     => $subjects,
            'source_url'   => "https://isbndb.com/book/{$fallbackIsbn}"
        ];
    }
}

