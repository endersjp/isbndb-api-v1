# API Scraper isbndb.com (PHP) - Documentación Técnica

Documentación y especificación técnica para el desarrollo de una API REST en **PHP** encargada de extraer, procesar y servir datos de libros desde **isbndb.com**.

---

## 📌 Objetivo del Proyecto

Crear una capa de servicios / API en PHP que consuma las páginas y endpoints de isbndb.com mediante **cURL** y procesamiento de DOM (**DOMDocument / DOMXPath** o librerías como **Symfony DomCrawler**), devolviendo respuestas estructuradas en formato **JSON**.

---

## 📁 Estructura de la Documentación

| Documento | Descripción | Endpoint Propuesto en PHP |
| :--- | :--- | :--- |
| [`getSearch.md`](getSearch.md) | Búsqueda por título, ISBN y paginación de resultados. | `GET /api/search.php?query={termino}&page={n}` |
| [`metadata.md`](metadata.md) | Mapeo y extracción de ficha técnica completa de un libro. | `GET /api/book.php?isbn={isbn13}` |

---

## ⚙️ Arquitectura Sugerida en PHP

```
api/
├── README.md               # Índice general y arquitectura
├── getSearch.md            # Especificación de búsquedas y paginación
├── metadata.md             # Especificación de metadatos de libro
│
├── config/
│   └── http.php            # Configuración de cURL y User-Agents comunes
├── services/
│   ├── HttpClient.php      # Wrapper de cURL con headers anti-bloqueo
│   ├── SearchScraper.php   # Lógica de scraping para búsquedas
│   └── BookScraper.php     # Lógica de scraping para metadatos del libro
├── search.php              # Endpoint público: búsqueda y paginación
└── book.php                # Endpoint público: detalle del libro
```

---

## 🛡️ Configuración de Cabeceras HTTP (cURL)

isbndb.com requiere encabezados de navegador estándar para evitar restricciones y bloqueos de navegación. En cada petición PHP cURL se recomienda utilizar la siguiente configuración base:

```php
<?php
function getHttpOptions(string $referer = 'https://isbndb.com/'): array {
    return [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'accept-language: es-ES,es;q=0.9,en;q=0.8',
            'referer: ' . $referer,
            'sec-ch-ua: "Chromium";v="124", "Google Chrome";v="124"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'sec-fetch-dest: document',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'upgrade-insecure-requests: 1',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
        ],
    ];
}
```

---

## 📦 Estándar de Respuestas JSON

Todas las respuestas generadas por la API PHP deben seguir una estructura consistente:

### Respuesta Exitosa (`200 OK`)
```json
{
  "status": "success",
  "code": 200,
  "data": {},
  "pagination": null
}
```

### Respuesta de Error (`4xx / 5xx`)
```json
{
  "status": "error",
  "code": 404,
  "message": "Libro no encontrado con el ISBN proporcionado",
  "data": null
}
```
