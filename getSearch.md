# Endpoint de Búsqueda y Paginación

Guía de especificación e implementación para el endpoint de **búsqueda de libros y paginación** a través de PHP consumiendo isbndb.com.

---

## 📋 Información General

- **Ruta sugerida en PHP:** `GET /api/search.php`
- **Formato de respuesta:** `application/json; charset=utf-8`
- **Descripción:** Permite buscar libros por título, palabras clave o código ISBN-13, además de paginar los resultados.

---

## 📥 Parámetros de Solicitud (Query Parameters)

| Parámetro | Tipo | Obligatorio | Por defecto | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `query` | `string` | **Sí** | - | Término de búsqueda (ej. `misterios`, `misterios de`, `9788433967855`). |
| `page` | `int` | No | `1` | Número de página de resultados a consultar. |

---

## 🌐 URLs de Origen (isbndb.com)

### 1. Búsqueda Inicial (Página 1)
- **Formato estándar con parámetros:**
  ```
  https://isbndb.com/search/books/?search_param=books&x={query}
  ```
  *Ejemplo:* `https://isbndb.com/search/books/?search_param=books&x=misterios+de`

- **Formato de ruta amigable:**
  ```
  https://isbndb.com/search/books/{query}
  ```
  *Ejemplos:*
  - `https://isbndb.com/search/books/misterios`
  - `https://isbndb.com/search/books/misterios%2Bde`

### 2. Paginación de Resultados (Páginas > 1 o AJAX)
Para cargar páginas subsiguientes, isbndb utiliza el endpoint interno `/books/more`:
```
https://isbndb.com/books/more?query={query}&column=&edition=&year=&language=&page={page}
```
*Ejemplos:*
- `https://isbndb.com/books/more?query=misterios&column=&edition=&year=&language=&page=3`
- `https://isbndb.com/books/more?query=misterios%20de&column=&edition=&year=&language=&page=2`

### 3. Búsqueda directa por ISBN-13
- Búsqueda: `https://isbndb.com/search/books?search_param=books&x=9788433967855`
- Redirección / Ficha directa: `https://isbndb.com/book/9788433967855`

---

## 🔒 Cabeceras Requeridas para cURL

Ejemplo de ejecución cURL con cabeceras para evitar bloqueos:

```bash
curl --url "https://isbndb.com/search/books/?search_param=books&x=9788433967855" \
  -H "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8" \
  -H "accept-language: es-ES,es;q=0.9,en;q=0.8" \
  -H "priority: u=0, i" \
  -H "referer: https://isbndb.com/" \
  -H "sec-ch-ua: \"Chromium\";v=\"124\", \"Google Chrome\";v=\"124\"" \
  -H "sec-ch-ua-mobile: ?0" \
  -H "sec-ch-ua-platform: \"Windows\"" \
  -H "sec-fetch-dest: document" \
  -H "sec-fetch-mode: navigate" \
  -H "sec-fetch-site: same-origin" \
  -H "sec-fetch-user: ?1" \
  -H "upgrade-insecure-requests: 1" \
  -H "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
```

---

## 📤 Estructura de Respuesta JSON (Salida de la API PHP)

```json
{
  "status": "success",
  "code": 200,
  "data": [
    {
      "isbn13": "9788433967855",
      "title": "Misterios",
      "authors": ["Knut Hamsun"],
      "publisher": "Anagrama",
      "publish_date": "2019-05-15",
      "binding": "Tapa blanda",
      "cover_image": "https://images.isbndb.com/covers/78/55/9788433967855.jpg",
      "detail_url": "/api/book.php?isbn=9788433967855"
    }
  ],
  "pagination": {
    "current_page": 1,
    "has_more": true,
    "next_page_url": "/api/search.php?query=misterios&page=2"
  }
}
```

---

## 💻 Ejemplo de Implementación en PHP (`search.php`)

```php
<?php
header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['query'] ?? '');
$page  = max(1, (int)($_GET['page'] ?? 1));

if (empty($query)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'code'    => 400,
        'message' => 'El parámetro "query" es requerido.'
    ]);
    exit;
}

// Determinar URL de origen (página 1 o paginación AJAX)
if ($page === 1) {
    $targetUrl = 'https://isbndb.com/search/books/?search_param=books&x=' . urlencode($query);
} else {
    $targetUrl = 'https://isbndb.com/books/more?query=' . urlencode($query) . '&column=&edition=&year=&language=&page=' . $page;
}

$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER     => [
        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'accept-language: es-ES,es;q=0.9,en;q=0.8',
        'referer: https://isbndb.com/',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    ]
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$html || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'code'    => 502,
        'message' => 'Error al obtener datos desde la fuente externa.'
    ]);
    exit;
}

// Procesar el HTML con DOMDocument y DOMXPath
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Lógica de extracción de tarjetas/resultados
$results = [];
// TODO: Iterar sobre nodos de libros (ej. //div[contains(@class, 'book-row')] o similar)

echo json_encode([
    'status'     => 'success',
    'code'       => 200,
    'data'       => $results,
    'pagination' => [
        'current_page'  => $page,
        'has_more'      => count($results) > 0,
        'next_page_url' => count($results) > 0 ? "/api/search.php?query=" . urlencode($query) . "&page=" . ($page + 1) : null
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```