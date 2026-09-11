# Endpoint de Metadatos del Libro

Especificación técnica y guía de extracción para el endpoint de **metadatos y ficha técnica completa de un libro** en PHP.

---

## 📋 Información General

- **Ruta sugerida en PHP:** `GET /api/book.php`
- **Formato de respuesta:** `application/json; charset=utf-8`
- **URL de origen (isbndb.com):** `https://isbndb.com/book/{isbn}` (ejemplo: `https://isbndb.com/book/9788417829759`)

---

## 📥 Parámetros de Solicitud (Query Parameters)

| Parámetro | Tipo | Obligatorio | Descripción |
| :--- | :--- | :--- | :--- |
| `isbn` | `string` | **Sí** | Código ISBN-13 o ISBN-10 del libro a consultar. |

---

## 🗺️ Mapeo de Campos (HTML ➡️ Modelo PHP / JSON)

Toda la ficha técnica se encuentra contenida en la tabla HTML `<table class="table is-hoverable table-container">`.

| Campo JSON | Etiqueta en HTML (`<th>`) | Selector / XPath sugerido | Tipo | Transformación / Formato |
| :--- | :--- | :--- | :--- | :--- |
| `title` | `Full Title:` | `//tr[th[contains(text(),'Full Title')]]/td` | `string` | Texto plano sin espacios redundantes. |
| `isbn13` | `ISBN13:` | `//tr[th[contains(text(),'ISBN13')]]/td` | `string` | Limpiar guiones o espacios. |
| `isbn10` | `ISBN10:` | `//tr[th[contains(text(),'ISBN10')]]/td` | `string` | Limpiar guiones o espacios. |
| `authors` | `Authors:` | `//tr[th[contains(text(),'Authors')]]/td//a` | `array<object>` | Extraer nombre (`text`) y ruta relativa (`href`). |
| `publisher` | `Publisher` | `//tr[th[contains(text(),'Publisher')]]/td` | `object` | Extraer nombre y ruta (`a/@href` si existe). |
| `publish_date`| `Publish Date:` | `//tr[th[contains(text(),'Publish Date')]]/td` | `string` | Normalizar a formato `YYYY-MM-DD`. |
| `binding` | `Binding:` | `//tr[th[contains(text(),'Binding')]]/td` | `string` | Ej: "Paperback", "Hardcover". |
| `pages` | `Pages:` | `//tr[th[contains(text(),'Pages')]]/td` | `int` | Cast numérico entero. |
| `synopsis` | `Synopsis:` | `//tr[th[contains(text(),'Synopsis')]]/td/p` | `string` | Limpiar etiquetas HTML residuales (`strip_tags`). |
| `language` | `Language:` | `//tr[th[contains(text(),'Language')]]/td` | `string` | Normalizar a formato ISO 639-1 (ej. `es`, `en`). |
| `dimensions` | `Dimensions:` | `//tr[th[contains(text(),'Dimensions')]]/td` | `string` | Dimensiones físicas (ej: `8.5 x 5.5 x 0.8 inches`). |
| `weight` | `Weight:` | `//tr[th[contains(text(),'Weight')]]/td` | `string` | Peso del libro. |
| `subjects` | `Subjects:` | `//tr[th[contains(text(),'Subjects')]]/td//ul/li/a` | `array<object>` | Lista de temas con `{ "name": "...", "path": "..." }`. |

---

## 🏛️ Estructura HTML de Referencia (isbndb.com)

```html
<table class="table is-hoverable table-container">
  <tr>
    <th>Full Title:</th>
    <td>Bajo las sombras...</td>
  </tr>
  <tr>
    <th>ISBN13:</th>
    <td>9788417829759</td>
  </tr>
  <tr>
    <th>ISBN10:</th>
    <td>8417829753</td>
  </tr>
  <tr>
    <th>Authors:</th>
    <td><a href="/author/autor-nombre">Nombre Autor</a><br /></td>
  </tr>
  <tr>
    <th>Publisher</th>
    <td><a href="/publisher/editorial">Editorial Ejemplo</a></td>
  </tr>
  <tr>
    <th>Publish Date:</th>
    <td>2020-03-10</td>
  </tr>
  <tr>
    <th>Binding:</th>
    <td>Paperback</td>
  </tr>
  <tr>
    <th>Pages:</th>
    <td>320</td>
  </tr>
  <tr>
    <th>Synopsis:</th>
    <td>
      <p>Texto descriptivo con posibles etiquetas <i>inline</i> o saltos de línea...</p>
    </td>
  </tr>
  <tr>
    <th>Language:</th>
    <td>Spanish</td>
  </tr>
  <tr>
    <th>Dimensions:</th>
    <td>8.5 x 5.5 x 0.8 inches</td>
  </tr>
  <tr>
    <th>Weight:</th>
    <td>1.1 pounds</td>
  </tr>
  <tr>
    <th>Subjects:</th>
    <td>
      <ul class="pl-10">
        <li><a href="/subject/ficcion">Ficción</a></li>
        <li><a href="/subject/misterio">Misterio</a></li>
      </ul>
    </td>
  </tr>
</table>
```

---

## 📤 Estructura de Respuesta JSON (Salida de la API PHP)

```json
{
  "status": "success",
  "code": 200,
  "data": {
    "title": "Bajo las sombras...",
    "isbn13": "9788417829759",
    "isbn10": "8417829753",
    "authors": [
      {
        "name": "Nombre Autor",
        "path": "/author/autor-nombre"
      }
    ],
    "publisher": {
      "name": "Editorial Ejemplo",
      "path": "/publisher/editorial"
    },
    "publish_date": "2020-03-10",
    "binding": "Paperback",
    "pages": 320,
    "synopsis": "Texto descriptivo con posibles etiquetas inline o saltos de línea...",
    "language": "es",
    "dimensions": "8.5 x 5.5 x 0.8 inches",
    "weight": "1.1 pounds",
    "subjects": [
      {
        "name": "Ficción",
        "path": "/subject/ficcion"
      },
      {
        "name": "Misterio",
        "path": "/subject/misterio"
      }
    ]
  }
}
```

---

## 💻 Ejemplo de Implementación en PHP (`book.php`)

```php
<?php
header('Content-Type: application/json; charset=utf-8');

$isbn = trim($_GET['isbn'] ?? '');

if (empty($isbn) || !preg_match('/^[0-9Xx\-]+$/', $isbn)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'code'    => 400,
        'message' => 'El parámetro "isbn" es inválido o no fue suministrado.'
    ]);
    exit;
}

$cleanIsbn = str_replace('-', '', $isbn);
$targetUrl = "https://isbndb.com/book/{$cleanIsbn}";

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

if (!$html || $httpCode === 404) {
    http_response_code(404);
    echo json_encode([
        'status'  => 'error',
        'code'    => 404,
        'message' => 'Libro no encontrado en isbndb.com'
    ]);
    exit;
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Helper para extraer texto directo de celda th -> td
$getTextByHeader = function(string $headerText) use ($xpath): ?string {
    $nodes = $xpath->query("//tr[th[contains(normalize-space(text()), '{$headerText}')]]/td");
    return ($nodes && $nodes->length > 0) ? trim($nodes->item(0)->textContent) : null;
};

// Extracción de Autores
$authors = [];
$authorNodes = $xpath->query("//tr[th[contains(normalize-space(text()), 'Authors')]]/td//a");
if ($authorNodes) {
    foreach ($authorNodes as $authorNode) {
        $authors[] = [
            'name' => trim($authorNode->textContent),
            'path' => $authorNode->getAttribute('href')
        ];
    }
}

// Extracción de Materias / Subjects
$subjects = [];
$subjectNodes = $xpath->query("//tr[th[contains(normalize-space(text()), 'Subjects')]]/td//ul/li/a");
if ($subjectNodes) {
    foreach ($subjectNodes as $subjectNode) {
        $subjects[] = [
            'name' => trim($subjectNode->textContent),
            'path' => $subjectNode->getAttribute('href')
        ];
    }
}

$data = [
    'title'        => $getTextByHeader('Full Title:'),
    'isbn13'       => $getTextByHeader('ISBN13:'),
    'isbn10'       => $getTextByHeader('ISBN10:'),
    'authors'      => $authors,
    'publisher'    => [
        'name' => $getTextByHeader('Publisher'),
        'path' => null
    ],
    'publish_date' => $getTextByHeader('Publish Date:'),
    'binding'      => $getTextByHeader('Binding:'),
    'pages'        => ($pages = $getTextByHeader('Pages:')) ? (int)$pages : null,
    'synopsis'     => $getTextByHeader('Synopsis:'),
    'language'     => $getTextByHeader('Language:'),
    'dimensions'   => $getTextByHeader('Dimensions:'),
    'weight'       => $getTextByHeader('Weight:'),
    'subjects'     => $subjects
];

echo json_encode([
    'status' => 'success',
    'code'   => 200,
    'data'   => $data
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```
