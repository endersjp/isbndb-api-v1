<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISBNdb Scraper API - v1</title>
  <meta name="description" content="API REST minimalista y moderna en PHP para consulta de libros y metadatos de isbndb.com">
  <style>
    :root {
      --bg-primary: #0a0c10;
      --bg-surface: #12161f;
      --bg-card: #181d28;
      --bg-card-hover: #1e2533;
      --border: #262f40;
      --border-focus: #3b82f6;
      --text-main: #f1f5f9;
      --text-muted: #94a3b8;
      --text-dim: #64748b;
      --accent: #3b82f6;
      --accent-glow: rgba(59, 130, 246, 0.15);
      --badge-bg: #1e293b;
      --badge-text: #93c5fd;
      --tag-bg: #1e293b;
      --success: #10b981;
      --error: #ef4444;
      --radius: 10px;
      --font: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    }

    [data-theme="light"] {
      --bg-primary: #f8fafc;
      --bg-surface: #ffffff;
      --bg-card: #ffffff;
      --bg-card-hover: #f1f5f9;
      --border: #e2e8f0;
      --border-focus: #2563eb;
      --text-main: #0f172a;
      --text-muted: #475569;
      --text-dim: #94a3b8;
      --accent: #2563eb;
      --accent-glow: rgba(37, 99, 235, 0.1);
      --badge-bg: #eff6ff;
      --badge-text: #1d4ed8;
      --tag-bg: #f1f5f9;
      --success: #059669;
      --error: #dc2626;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-color: var(--bg-primary);
      color: var(--text-main);
      font-family: var(--font);
      line-height: 1.5;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      transition: background-color 0.2s ease, color 0.2s ease;
    }

    /* Header */
    header {
      background-color: var(--bg-surface);
      border-bottom: 1px solid var(--border);
      padding: 1rem 1.5rem;
      position: sticky;
      top: 0;
      z-index: 100;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .brand-logo {
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, var(--accent), #8b5cf6);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #fff;
      font-size: 0.9rem;
    }

    .brand-title {
      font-size: 1.15rem;
      font-weight: 600;
      letter-spacing: -0.02em;
    }

    .badge-version {
      font-size: 0.75rem;
      font-weight: 600;
      background: var(--badge-bg);
      color: var(--badge-text);
      padding: 0.2rem 0.5rem;
      border-radius: 9999px;
      border: 1px solid var(--border);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .btn-icon {
      background: var(--bg-card);
      border: 1px solid var(--border);
      color: var(--text-muted);
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1rem;
      transition: all 0.15s ease;
    }

    .btn-icon:hover {
      color: var(--text-main);
      border-color: var(--border-focus);
    }

    /* Main Container */
    main {
      flex: 1;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 2rem 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    /* Hero / Search Section */
    .hero-section {
      text-align: center;
      max-width: 680px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .hero-title {
      font-size: 2.25rem;
      font-weight: 700;
      letter-spacing: -0.03em;
    }

    .hero-subtitle {
      color: var(--text-muted);
      font-size: 1rem;
    }

    .search-box {
      margin-top: 1rem;
      display: flex;
      gap: 0.5rem;
      background: var(--bg-surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 0.35rem 0.5rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: border-color 0.15s ease;
    }

    .search-box:focus-within {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 3px var(--accent-glow);
    }

    .search-input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text-main);
      font-size: 0.95rem;
      padding: 0.5rem 0.75rem;
      font-family: inherit;
    }

    .search-input::placeholder {
      color: var(--text-dim);
    }

    .btn {
      background-color: var(--accent);
      color: #ffffff;
      border: none;
      border-radius: calc(var(--radius) - 2px);
      padding: 0.6rem 1.25rem;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: opacity 0.15s ease, transform 0.1s ease;
    }

    .btn:hover {
      opacity: 0.92;
    }

    .btn:active {
      transform: scale(0.98);
    }

    .btn-secondary {
      background-color: var(--bg-card);
      color: var(--text-main);
      border: 1px solid var(--border);
    }

    .btn-secondary:hover {
      background-color: var(--bg-card-hover);
      border-color: var(--border-focus);
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: 0.5rem;
      border-bottom: 1px solid var(--border);
      margin-top: 1rem;
    }

    .tab-btn {
      background: transparent;
      border: none;
      outline: none;
      padding: 0.75rem 1.25rem;
      color: var(--text-muted);
      font-size: 0.95rem;
      font-weight: 500;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: all 0.15s ease;
    }

    .tab-btn:hover {
      color: var(--text-main);
    }

    .tab-btn.active {
      color: var(--accent);
      border-bottom-color: var(--accent);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* Grid de Libros */
    .books-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1.5rem;
    }

    .book-card {
      background-color: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .book-card:hover {
      transform: translateY(-3px);
      border-color: var(--border-focus);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .book-cover {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: calc(var(--radius) - 4px);
      background-color: var(--bg-surface);
      border: 1px solid var(--border);
    }

    .book-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .book-isbn {
      font-family: var(--font-mono);
      font-size: 0.75rem;
      color: var(--accent);
      font-weight: 600;
    }

    .book-title {
      font-size: 1.05rem;
      font-weight: 600;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .book-author {
      font-size: 0.85rem;
      color: var(--text-muted);
    }

    /* State messages */
    .empty-state {
      text-align: center;
      padding: 3.5rem 1rem;
      color: var(--text-muted);
    }

    .empty-icon {
      font-size: 2.5rem;
      margin-bottom: 0.75rem;
      opacity: 0.6;
    }

    /* Endpoint Explorer */
    .endpoints-list {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .endpoint-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .endpoint-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .method-badge {
      background: #0284c7;
      color: #fff;
      font-size: 0.75rem;
      font-weight: 700;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      font-family: var(--font-mono);
    }

    .endpoint-path {
      font-family: var(--font-mono);
      font-weight: 600;
      font-size: 0.95rem;
    }

    .endpoint-desc {
      font-size: 0.875rem;
      color: var(--text-muted);
    }

    .code-box {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 1rem;
      font-family: var(--font-mono);
      font-size: 0.85rem;
      overflow-x: auto;
      max-height: 360px;
      white-space: pre;
    }

    /* Modal Dialog */
    dialog {
      margin: auto;
      background: var(--bg-surface);
      color: var(--text-main);
      border: 1px solid var(--border);
      border-radius: 14px;
      max-width: 650px;
      width: 90%;
      padding: 1.5rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    dialog::backdrop {
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border);
    }

    .modal-title {
      font-size: 1.25rem;
      font-weight: 700;
    }

    .meta-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.875rem;
      margin-top: 1rem;
    }

    .meta-table th {
      text-align: left;
      color: var(--text-dim);
      font-weight: 500;
      padding: 0.5rem 0.5rem 0.5rem 0;
      width: 32%;
      vertical-align: top;
    }

    .meta-table td {
      padding: 0.5rem 0;
      color: var(--text-main);
    }

    .tag-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.35rem;
    }

    .tag-chip {
      background: var(--tag-bg);
      border: 1px solid var(--border);
      font-size: 0.75rem;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
    }

    /* Footer */
    footer {
      border-top: 1px solid var(--border);
      padding: 1.25rem 1.5rem;
      text-align: center;
      color: var(--text-dim);
      font-size: 0.85rem;
    }
  </style>
</head>
<body>

  <header>
    <div class="brand">
      <div class="brand-logo">📚</div>
      <span class="brand-title">ISBNdb API</span>
      <span class="badge-version">v1.0 (PHP)</span>
    </div>
    <div class="header-actions">
      <button class="btn-icon" id="btnThemeToggle" title="Cambiar Tema" aria-label="Cambiar Tema">🌓</button>
    </div>
  </header>

  <main>
    <section class="hero-section">
      <h1 class="hero-title">Buscador & Metadatos de Libros</h1>
      <p class="hero-subtitle">API modular construida en PHP sin dependencias pesadas. Consume endpoints limpios en JSON.</p>
      
      <form id="searchForm" class="search-box">
        <input 
          type="text" 
          id="searchInput" 
          class="search-input" 
          placeholder="Busca por título o ISBN (ej. misterios o 9788433967855)..."
          autocomplete="off"
          required
        >
        <button type="submit" class="btn" id="btnSearch">Buscar</button>
      </form>
    </section>

    <div class="tabs">
      <button class="tab-btn active" data-tab="explorer">Explorador Interactivo</button>
      <button class="tab-btn" data-tab="endpoints">Documentación de Endpoints</button>
      <button class="tab-btn" data-tab="jsonViewer">Respuesta JSON en Vivo</button>
    </div>

    <!-- TAB 1: Explorador -->
    <div id="tab-explorer" class="tab-content active">
      <div id="loadingIndicator" class="empty-state" style="display: none;">
        <div class="empty-icon">⏳</div>
        <p>Consultando API PHP...</p>
      </div>

      <div id="resultsContainer" class="books-grid"></div>

      <div id="emptyState" class="empty-state">
        <div class="empty-icon">🔍</div>
        <p>Ingresa un término de búsqueda o código ISBN arriba para ver los resultados.</p>
      </div>

      <div id="paginationControls" style="display: none; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
        <button class="btn btn-secondary" id="btnPrevPage" disabled>&larr; Anterior</button>
        <span id="pageLabel" style="display: flex; align-items: center; padding: 0 1rem; font-size: 0.9rem; color: var(--text-muted);">Página 1</span>
        <button class="btn btn-secondary" id="btnNextPage">Siguiente &rarr;</button>
      </div>
    </div>

    <!-- TAB 2: Endpoints -->
    <div id="tab-endpoints" class="tab-content">
      <div class="endpoints-list">
        
        <div class="endpoint-card">
          <div class="endpoint-header">
            <span class="method-badge">GET</span>
            <span class="endpoint-path">/v1/search.php?query={termino}&page={n}</span>
          </div>
          <p class="endpoint-desc">Busca libros por título o código ISBN y retorna resultados paginados.</p>
          <div class="code-box">curl -X GET "http://localhost/v1/search.php?query=misterios&page=1"</div>
        </div>

        <div class="endpoint-card">
          <div class="endpoint-header">
            <span class="method-badge">GET</span>
            <span class="endpoint-path">/v1/book.php?isbn={isbn13}</span>
          </div>
          <p class="endpoint-desc">Obtiene la ficha técnica completa y metadatos detallados de un libro.</p>
          <div class="code-box">curl -X GET "http://localhost/v1/book.php?isbn=9788433967855"</div>
        </div>

      </div>
    </div>

    <!-- TAB 3: JSON Viewer -->
    <div id="tab-jsonViewer" class="tab-content">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <span style="font-size: 0.9rem; color: var(--text-muted);">Última carga de la API:</span>
        <button class="btn btn-secondary" id="btnCopyJson" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Copiar JSON</button>
      </div>
      <pre id="jsonLiveCode" class="code-box">// Los datos JSON de las peticiones aparecerán aquí...</pre>
    </div>
  </main>

  <!-- Modal de Detalle de Libro -->
  <dialog id="bookDialog">
    <div class="modal-header">
      <h3 class="modal-title" id="modalBookTitle">Detalle del Libro</h3>
      <button class="btn-icon" id="btnCloseModal" aria-label="Cerrar">&times;</button>
    </div>
    <div id="modalLoading" class="empty-state" style="padding: 1.5rem 0;">
      <p>Cargando metadatos...</p>
    </div>
    <div id="modalContent" style="display: none;">
      <div style="display: flex; gap: 1.25rem; margin-bottom: 1rem;">
        <img id="modalCover" src="" alt="Portada" style="width: 110px; height: 160px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);">
        <div style="flex: 1;">
          <h4 id="modalFullTitle" style="font-size: 1.1rem; margin-bottom: 0.25rem;"></h4>
          <p id="modalAuthors" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"></p>
          <span id="modalIsbn" class="badge-version"></span>
        </div>
      </div>
      <table class="meta-table">
        <tbody>
          <tr><th>Editorial:</th><td id="metaPublisher">-</td></tr>
          <tr><th>Fecha de publicación:</th><td id="metaDate">-</td></tr>
          <tr><th>Páginas:</th><td id="metaPages">-</td></tr>
          <tr><th>Encuadernación:</th><td id="metaBinding">-</td></tr>
          <tr><th>Idioma:</th><td id="metaLanguage">-</td></tr>
          <tr><th>Dimensiones / Peso:</th><td id="metaDimensions">-</td></tr>
          <tr><th>Materias:</th><td id="metaSubjects">-</td></tr>
        </tbody>
      </table>
      <div style="margin-top: 1rem;">
        <h5 style="font-size: 0.9rem; margin-bottom: 0.35rem; color: var(--text-dim);">Sinopsis:</h5>
        <p id="metaSynopsis" style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;"></p>
      </div>
    </div>
  </dialog>

  <footer>
    <p>ISBNdb Scraper API v1.0 &bull; Diseñado en PHP nativo &bull; Minimalista y modular</p>
  </footer>

  <script>
    // State
    let currentQuery = '';
    let currentPage = 1;
    let lastJsonResponse = null;

    // Elements
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('resultsContainer');
    const emptyState = document.getElementById('emptyState');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const paginationControls = document.getElementById('paginationControls');
    const pageLabel = document.getElementById('pageLabel');
    const btnPrevPage = document.getElementById('btnPrevPage');
    const btnNextPage = document.getElementById('btnNextPage');
    const jsonLiveCode = document.getElementById('jsonLiveCode');
    const btnCopyJson = document.getElementById('btnCopyJson');

    const bookDialog = document.getElementById('bookDialog');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');

    // Tabs
    document.querySelectorAll('.tab-btn').forEach(button => {
      button.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(`tab-${button.dataset.tab}`).classList.add('active');
      });
    });

    // Theme Toggle
    const btnThemeToggle = document.getElementById('btnThemeToggle');
    btnThemeToggle.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme');
      const next = current === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
    });

    // Search Action
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const query = searchInput.value.trim();
      if (!query) return;
      currentQuery = query;
      currentPage = 1;
      executeSearch(currentQuery, currentPage);
    });

    btnPrevPage.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        executeSearch(currentQuery, currentPage);
      }
    });

    btnNextPage.addEventListener('click', () => {
      currentPage++;
      executeSearch(currentQuery, currentPage);
    });

    async function executeSearch(query, page) {
      emptyState.style.display = 'none';
      resultsContainer.innerHTML = '';
      loadingIndicator.style.display = 'block';

      try {
        const res = await fetch(`search.php?query=${encodeURIComponent(query)}&page=${page}`);
        const data = await res.json();
        lastJsonResponse = data;
        jsonLiveCode.textContent = JSON.stringify(data, null, 2);

        loadingIndicator.style.display = 'none';

        if (data.status === 'success' && data.data && data.data.length > 0) {
          renderBooks(data.data);
          paginationControls.style.display = 'flex';
          pageLabel.textContent = `Página ${page}`;
          btnPrevPage.disabled = page <= 1;
          btnNextPage.disabled = !data.pagination?.has_more;
        } else {
          emptyState.style.display = 'block';
          emptyState.querySelector('p').textContent = data.message || 'No se encontraron resultados para esta búsqueda.';
          paginationControls.style.display = 'none';
        }
      } catch (err) {
        loadingIndicator.style.display = 'none';
        emptyState.style.display = 'block';
        emptyState.querySelector('p').textContent = 'Error al conectar con la API de PHP.';
      }
    }

    function renderBooks(books) {
      resultsContainer.innerHTML = '';
      books.forEach(book => {
        const card = document.createElement('div');
        card.className = 'book-card';

        const coverSrc = book.cover_image || 'https://via.placeholder.com/300x400?text=Sin+Portada';
        const authors = Array.isArray(book.authors) && book.authors.length > 0 ? book.authors.join(', ') : 'Autor desconocido';

        card.innerHTML = `
          <img class="book-cover" src="${escapeHtml(coverSrc)}" alt="Portada de ${escapeHtml(book.title)}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x400?text=Sin+Portada'">
          <div class="book-info">
            <span class="book-isbn">ISBN: ${escapeHtml(book.isbn13)}</span>
            <h3 class="book-title">${escapeHtml(book.title)}</h3>
            <p class="book-author">${escapeHtml(authors)}</p>
          </div>
          <button class="btn btn-secondary" style="width: 100%; justify-content: center;" onclick="openBookModal('${escapeHtml(book.isbn13)}')">
            Ver Ficha Completa
          </button>
        `;
        resultsContainer.appendChild(card);
      });
    }

    window.openBookModal = async function(isbn) {
      bookDialog.showModal();
      modalLoading.style.display = 'block';
      modalContent.style.display = 'none';

      try {
        const res = await fetch(`book.php?isbn=${encodeURIComponent(isbn)}`);
        const result = await res.json();
        
        lastJsonResponse = result;
        jsonLiveCode.textContent = JSON.stringify(result, null, 2);

        if (result.status === 'success' && result.data) {
          const b = result.data;
          document.getElementById('modalFullTitle').textContent = b.title || 'Título no disponible';
          document.getElementById('modalAuthors').textContent = Array.isArray(b.authors) ? b.authors.map(a => a.name || a).join(', ') : '-';
          document.getElementById('modalIsbn').textContent = `ISBN-13: ${b.isbn13 || isbn}`;
          document.getElementById('modalCover').src = b.cover_image || 'https://via.placeholder.com/200x280?text=No+Cover';
          
          document.getElementById('metaPublisher').textContent = b.publisher?.name || '-';
          document.getElementById('metaDate').textContent = b.publish_date || '-';
          document.getElementById('metaPages').textContent = b.pages ? `${b.pages} págs.` : '-';
          document.getElementById('metaBinding').textContent = b.binding || '-';
          document.getElementById('metaLanguage').textContent = b.language || '-';
          
          const dimWeight = [b.dimensions, b.weight].filter(Boolean).join(' / ');
          document.getElementById('metaDimensions').textContent = dimWeight || '-';

          const subjectsContainer = document.getElementById('metaSubjects');
          if (Array.isArray(b.subjects) && b.subjects.length > 0) {
            subjectsContainer.innerHTML = `<div class="tag-list">${b.subjects.map(s => `<span class="tag-chip">${escapeHtml(s.name || s)}</span>`).join('')}</div>`;
          } else {
            subjectsContainer.textContent = '-';
          }

          document.getElementById('metaSynopsis').textContent = b.synopsis || 'Sinopsis no disponible.';

          modalLoading.style.display = 'none';
          modalContent.style.display = 'block';
        } else {
          modalLoading.innerHTML = `<p style="color: var(--error);">${result.message || 'Error al cargar el libro.'}</p>`;
        }
      } catch (e) {
        modalLoading.innerHTML = '<p style="color: var(--error);">Error al conectar con el servidor.</p>';
      }
    };

    btnCloseModal.addEventListener('click', () => bookDialog.close());
    bookDialog.addEventListener('click', (e) => {
      if (e.target === bookDialog) bookDialog.close();
    });

    btnCopyJson.addEventListener('click', () => {
      if (lastJsonResponse) {
        navigator.clipboard.writeText(JSON.stringify(lastJsonResponse, null, 2));
        btnCopyJson.textContent = '¡Copiado!';
        setTimeout(() => btnCopyJson.textContent = 'Copiar JSON', 2000);
      }
    });

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[m]));
    }
  </script>
</body>
</html>

