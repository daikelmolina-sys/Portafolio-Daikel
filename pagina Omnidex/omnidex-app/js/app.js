/* ==========================================================================
   app.js — Orquestador Principal del Frontend (Eventos, Búsqueda, Estado)
   ==========================================================================
   Coordina:
   1. Inicialización y enlace de eventos del DOM al cargar la página.
   2. Búsqueda con debounce para evitar sobrecarga de consultas a la API.
   3. Carga de criaturas destacadas y filtrado por tipos elementales.
   4. Interacción con el modal holográfico y alternancia de temas Claro/Oscuro.
   ========================================================================== */

(function OmnidexApp() {
  'use strict';

  // =========================================================================
  // ESTADO LOCAL DE LA APLICACIÓN
  // =========================================================================
  let currentSearch = ''; // Término de búsqueda actualmente ejecutado
  let isSearching = false; // Flag para evitar búsquedas concurrentes redundantes

  // =========================================================================
  // UTILIDAD: DEBOUNCE (Limita la frecuencia de ejecución de funciones)
  // =========================================================================
  function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  // =========================================================================
  // INICIALIZACIÓN
  // =========================================================================
  document.addEventListener('DOMContentLoaded', async () => {
    // 1. Cachear referencias del DOM
    OmnidexUI.cacheDom();

    // 2. Inicializar efecto 3D holográfico con el ratón
    TiltEffect.init('#resultsGrid', '.poke-card');

    // 3. Vincular los escuchadores de eventos
    bindSearchEvents();
    bindDetailEvents();
    bindRetryEvent();
    bindNavEvents();
    bindLogoEvent();
    bindThemeToggle();

    // 4. Cargar botones de filtro por tipo
    loadTypeFilters();

    // 5. Cargar colección inicial de Pokémon destacados
    await loadFeatured();
  });

  // =========================================================================
  // CARGA DE POKÉMON DESTACADOS (VISTA INICIAL)
  // =========================================================================
  async function loadFeatured() {
    OmnidexUI.showSkeletons(12);
    OmnidexUI.showLoading();
    try {
      const featured = await PokeAPI.getFeatured(12);
      OmnidexUI.renderCards(featured);
      OmnidexUI.hideLoading();
    } catch (err) {
      OmnidexUI.hideLoading();
      OmnidexUI.showError(err);
    }
  }

  // =========================================================================
  // FILTRADO POR TIPOS ELEMENTALES
  // =========================================================================
  async function loadTypeFilters() {
    try {
      const types = await PokeAPI.getTypeList();
      OmnidexUI.renderTypeFilters(types, handleTypeFilter);
    } catch (_) {
      // Si falla la carga de tipos, no interrumpe el funcionamiento general
    }
  }

  async function handleTypeFilter(typeName) {
    if (!typeName) {
      await loadFeatured();
      ThemeEngine.resetTheme();
      return;
    }

    OmnidexUI.showSkeletons(12);
    OmnidexUI.showLoading();

    try {
      const pokemonRefs = await PokeAPI.getPokemonByType(typeName);
      // Tomar los primeros 24 Pokémon del tipo seleccionado
      const slice = pokemonRefs.slice(0, 24);
      const details = await Promise.all(
        slice.map(p => PokeAPI.getPokemonDetails(p.name))
      );
      OmnidexUI.renderCards(details);
      OmnidexUI.hideLoading();
    } catch (err) {
      OmnidexUI.hideLoading();
      OmnidexUI.showError(err);
    }
  }

  // =========================================================================
  // GESTIÓN DE BÚSQUEDAS (Inputs Hero y Navbar sincronizados)
  // =========================================================================
  function bindSearchEvents() {
    const heroInput = document.getElementById('heroSearchInput');
    const headerInput = document.getElementById('headerSearch');

    // Sincronizar el texto entre el buscador principal y el del navbar
    const syncInputs = (source, target) => {
      target.value = source.value;
    };

    // Búsqueda con retardo (debounce) para no saturar al escribir
    const debouncedSearch = debounce(async (query) => {
      if (!query.trim()) {
        await loadFeatured();
        ThemeEngine.resetTheme();
        return;
      }
      await performSearch(query);
    }, 400);

    // Eventos en input central (Hero)
    heroInput.addEventListener('input', (e) => {
      syncInputs(heroInput, headerInput);
      debouncedSearch(e.target.value);
    });

    heroInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        performSearch(heroInput.value);
      }
    });

    // Eventos en input del Navbar
    headerInput.addEventListener('input', (e) => {
      syncInputs(headerInput, heroInput);
      debouncedSearch(e.target.value);
    });

    headerInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        performSearch(headerInput.value);
      }
    });

    // Atajo de teclado global: Presionar "/" para enfocar la barra de búsqueda
    document.addEventListener('keydown', (e) => {
      if (e.key === '/' && document.activeElement !== heroInput && document.activeElement !== headerInput) {
        e.preventDefault();
        heroInput.focus();
      }
    });
  }

  /**
   * Ejecuta la consulta de búsqueda contra la PokéAPI.
   */
  async function performSearch(query) {
    const q = String(query).trim().toLowerCase();
    if (!q || q === currentSearch || isSearching) return;
    currentSearch = q;
    isSearching = true;

    // Desactivar filtros de tipo activos
    document.querySelectorAll('.type-pill').forEach(p => p.classList.remove('type-pill--active'));

    OmnidexUI.showSkeletons(4);
    OmnidexUI.showLoading();

    try {
      const pokemon = await PokeAPI.searchPokemon(q);
      if (pokemon) {
        OmnidexUI.renderCards([pokemon]);
        // Aplicar paleta de colores reactiva desde el sprite
        const sprite = pokemon.sprites?.other?.['official-artwork']?.front_default
          || pokemon.sprites?.front_default;
        if (sprite) {
          ThemeEngine.applyFromImage(sprite);
        }
      } else {
        OmnidexUI.renderCards([]);
      }
      OmnidexUI.hideLoading();
    } catch (err) {
      OmnidexUI.hideLoading();
      if (err.type === 'NOT_FOUND') {
        OmnidexUI.renderCards([]); // Mostrar estado vacío
      } else {
        OmnidexUI.showError(err);
      }
    } finally {
      isSearching = false;
    }
  }

  // =========================================================================
  // MODAL DE DETALLE HOLOGRÁFICO
  // =========================================================================
  function bindDetailEvents() {
    const grid = document.getElementById('resultsGrid');
    const overlay = document.getElementById('detailOverlay');
    const closeBtn = document.getElementById('detailClose');

    // Al hacer clic en una tarjeta, consultar y abrir modal
    grid.addEventListener('click', async (e) => {
      const card = e.target.closest('.poke-card');
      if (!card) return;

      const id = card.dataset.pokemonId;
      if (!id) return;

      OmnidexUI.showLoading();
      try {
        const pokemon = await PokeAPI.getPokemonDetails(id);
        OmnidexUI.showDetail(pokemon);
      } catch (err) {
        console.error('Error al cargar detalle:', err);
      }
      OmnidexUI.hideLoading();
    });

    // Cerrar modal con botón X
    closeBtn.addEventListener('click', () => {
      OmnidexUI.hideDetail();
      ThemeEngine.resetTheme();
    });

    // Cerrar al hacer clic fuera del panel (fondo oscuro)
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        OmnidexUI.hideDetail();
        ThemeEngine.resetTheme();
      }
    });

    // Cerrar al presionar la tecla Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        OmnidexUI.hideDetail();
        ThemeEngine.resetTheme();
      }
    });
  }

  // =========================================================================
  // REINTENTO DE CONEXIÓN TRAS ERROR
  // =========================================================================
  function bindRetryEvent() {
    const btn = document.getElementById('retryBtn');
    btn.addEventListener('click', async () => {
      OmnidexUI.hideError();
      if (currentSearch) {
        await performSearch(currentSearch);
      } else {
        await loadFeatured();
      }
    });
  }

  // =========================================================================
  // BOTONES DE NAVEGACIÓN
  // =========================================================================
  function bindNavEvents() {
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        navBtns.forEach(b => b.classList.remove('nav-btn--active'));
        btn.classList.add('nav-btn--active');
      });
    });
  }

  // =========================================================================
  // LOGO: REINICIAR AL INICIO
  // =========================================================================
  function bindLogoEvent() {
    const logo = document.getElementById('logoHome');
    logo.addEventListener('click', async (e) => {
      e.preventDefault();
      document.getElementById('heroSearchInput').value = '';
      document.getElementById('headerSearch').value = '';
      currentSearch = '';
      document.querySelectorAll('.type-pill').forEach(p => p.classList.remove('type-pill--active'));
      ThemeEngine.resetTheme();
      await loadFeatured();
    });
  }

  // =========================================================================
  // ALTERNANCIA DE TEMA CLARO / OSCURO (Con persistencia en LocalStorage)
  // =========================================================================
  function bindThemeToggle() {
    const btn = document.getElementById('themeToggle');

    // Recuperar preferencia previa del usuario
    const saved = localStorage.getItem('omnidex-theme');
    if (saved === 'light') {
      document.documentElement.setAttribute('data-theme', 'light');
    }

    btn.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme');
      const next = current === 'light' ? 'dark' : 'light';

      if (next === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      } else {
        document.documentElement.removeAttribute('data-theme');
      }

      localStorage.setItem('omnidex-theme', next);
    });
  }

})();

