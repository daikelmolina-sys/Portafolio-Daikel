/* ==========================================================================
   ui.js — Renderizador Visual de Interfaz (DOM, Skeleton Loaders, Modales)
   ==========================================================================
   Se encarga de:
   - Administrar animaciones de carga (Skeleton screens y barra de progreso).
   - Generar dinámicamente las tarjetas interactivas de Pokémon con sus tipos.
   - Construir y mostrar el modal / overlay holográfico con stats detallados.
   - Manejar pantallas de estado vacío (Empty state) y errores de red.
   ========================================================================== */

const OmnidexUI = (() => {
  // Mapa de colores oficiales para badges de cada tipo elemental
  const TYPE_COLORS = {
    normal:   '#A8A878', fire:     '#F08030', water:    '#6890F0',
    electric: '#F8D030', grass:    '#78C850', ice:      '#98D8D8',
    fighting: '#C03028', poison:   '#A040A0', ground:   '#E0C068',
    flying:   '#A890F0', psychic:  '#F85888', bug:      '#A8B820',
    rock:     '#B8A038', ghost:    '#705898', dragon:   '#7038F8',
    dark:     '#705848', steel:    '#B8B8D0', fairy:    '#EE99AC',
  };

  // Abreviaciones amigables para las estadísticas de combate
  const STAT_LABELS = {
    'hp': 'HP', 'attack': 'ATK', 'defense': 'DEF',
    'special-attack': 'SPA', 'special-defense': 'SPD', 'speed': 'SPE',
  };

  // Referencias a elementos del DOM cacheadas
  const els = {};

  /**
   * Cachea las referencias a los elementos del DOM para evitar consultas repetitivas con getElementById.
   */
  function cacheDom() {
    els.resultsGrid  = document.getElementById('resultsGrid');
    els.emptyState   = document.getElementById('emptyState');
    els.errorPanel   = document.getElementById('errorPanel');
    els.errorCode    = document.getElementById('errorCode');
    els.loadingBar   = document.getElementById('loadingBar');
    els.detailOverlay= document.getElementById('detailOverlay');
    els.detailPanel  = document.getElementById('detailPanel');
    els.detailSprite = document.getElementById('detailSprite');
    els.detailName   = document.getElementById('detailName');
    els.detailId     = document.getElementById('detailId');
    els.detailTypes  = document.getElementById('detailTypes');
    els.detailHeight = document.getElementById('detailHeight');
    els.detailWeight = document.getElementById('detailWeight');
    els.detailExp    = document.getElementById('detailExp');
    els.detailStats  = document.getElementById('detailStats');
    els.detailAbilities = document.getElementById('detailAbilities');
    els.typeFilters  = document.getElementById('typeFilters');
  }

  /* --------------------------------------------------------------------------
     1. BARRA SUPERIOR DE CARGA
     -------------------------------------------------------------------------- */
  function showLoading() {
    els.loadingBar.classList.add('loading-bar--active');
    els.loadingBar.style.width = '30%';
    setTimeout(() => { els.loadingBar.style.width = '70%'; }, 300);
  }

  function hideLoading() {
    els.loadingBar.style.width = '100%';
    setTimeout(() => {
      els.loadingBar.classList.remove('loading-bar--active');
      els.loadingBar.style.width = '0';
    }, 400);
  }

  /* --------------------------------------------------------------------------
     2. PANTALLAS DE CARGA ESQUELÉTICAS (SKELETON SCREENS)
     -------------------------------------------------------------------------- */
  function showSkeletons(count = 12) {
    clearResults();
    hideError();
    hideEmpty();

    let html = '';
    for (let i = 0; i < count; i++) {
      html += `
        <div class="skeleton-card reveal reveal-delay-${Math.min(i + 1, 12)}">
          <div class="skeleton-el skeleton-circle"></div>
          <div class="skeleton-el skeleton-line skeleton-line--short"></div>
          <div class="skeleton-el skeleton-line--xs skeleton-el" style="width:50%;margin:8px auto 0;height:10px;"></div>
        </div>
      `;
    }
    els.resultsGrid.innerHTML = html;
  }

  function hideSkeletons() {
    const skeletons = els.resultsGrid.querySelectorAll('.skeleton-card');
    skeletons.forEach(s => {
      s.style.opacity = '0';
      s.style.transform = 'scale(0.95)';
    });
    setTimeout(() => {
      skeletons.forEach(s => s.remove());
    }, 300);
  }

  /* --------------------------------------------------------------------------
     3. RENDERIZADO DE TARJETAS DE POKÉMON
     -------------------------------------------------------------------------- */
  function renderCards(pokemonList) {
    hideSkeletons();
    hideError();

    if (!pokemonList || pokemonList.length === 0) {
      showEmpty();
      return;
    }

    hideEmpty();

    // Pequeño retardo para dar suavidad visual a la transición de salida del skeleton
    setTimeout(() => {
      let html = '';
      pokemonList.forEach((p, i) => {
        const sprite = p.sprites?.other?.['official-artwork']?.front_default
          || p.sprites?.front_default
          || '';
        const types = (p.types || []).map(t =>
          `<span class="poke-card__type-badge" style="--badge-color:${TYPE_COLORS[t.type.name] || '#888'}">${t.type.name}</span>`
        ).join('');
        const id = String(p.id).padStart(4, '0');

        html += `
          <div class="poke-card reveal reveal-delay-${Math.min(i + 1, 12)}" data-pokemon-id="${p.id}" data-sprite="${sprite}">
            <span class="poke-card__id">#${id}</span>
            <div class="poke-card__sprite-wrapper">
              <img class="poke-card__sprite" src="${sprite}" alt="${p.name}" loading="lazy" />
            </div>
            <h3 class="poke-card__name">${p.name}</h3>
            <div class="poke-card__types">${types}</div>
          </div>
        `;
      });

      els.resultsGrid.innerHTML = html;
    }, 250);
  }

  /* --------------------------------------------------------------------------
     4. PANEL / MODAL DE DETALLES HOLOGRÁFICO
     -------------------------------------------------------------------------- */
  function showDetail(pokemon) {
    const sprite = pokemon.sprites?.other?.['official-artwork']?.front_default
      || pokemon.sprites?.front_default
      || '';

    els.detailSprite.src = sprite;
    els.detailSprite.alt = pokemon.name;
    els.detailName.textContent = pokemon.name;
    els.detailId.textContent = `#${String(pokemon.id).padStart(4, '0')}`;

    // Tipos elementales
    els.detailTypes.innerHTML = (pokemon.types || []).map(t =>
      `<span class="poke-card__type-badge" style="--badge-color:${TYPE_COLORS[t.type.name] || '#888'}">${t.type.name}</span>`
    ).join('');

    // Metadatos de altura, peso y experiencia
    els.detailHeight.textContent = `📏 ${(pokemon.height / 10).toFixed(1)} m`;
    els.detailWeight.textContent = `⚖️ ${(pokemon.weight / 10).toFixed(1)} kg`;
    els.detailExp.textContent = `✨ ${pokemon.base_experience || '—'} EXP`;

    // Barras de estadísticas
    const statsHTML = (pokemon.stats || []).map(s => {
      const label = STAT_LABELS[s.stat.name] || s.stat.name;
      const value = s.base_stat;
      const percent = Math.min((value / 255) * 100, 100).toFixed(1);
      return `
        <div class="stat-row">
          <span class="stat-row__label">${label}</span>
          <div class="stat-row__bar">
            <div class="stat-row__fill" data-width="${percent}%"></div>
          </div>
          <span class="stat-row__value">${value}</span>
        </div>
      `;
    }).join('');
    els.detailStats.innerHTML = `<h3 class="detail-stats__title">Estadísticas Base</h3>${statsHTML}`;

    // Habilidades
    const abilitiesHTML = (pokemon.abilities || []).map(a => {
      const cls = a.is_hidden ? 'ability-chip ability-chip--hidden' : 'ability-chip';
      const label = a.is_hidden ? `${a.ability.name} (oculta)` : a.ability.name;
      return `<span class="${cls}">${label}</span>`;
    }).join('');
    els.detailAbilities.innerHTML = `<h3 class="detail-abilities__title">Habilidades</h3>${abilitiesHTML}`;

    // Mostrar overlay y bloquear scroll del fondo
    els.detailOverlay.classList.add('detail-overlay--visible');
    document.body.style.overflow = 'hidden';

    // Animar las barras de progreso de stats tras abrir
    requestAnimationFrame(() => {
      setTimeout(() => {
        els.detailStats.querySelectorAll('.stat-row__fill').forEach(bar => {
          bar.style.width = bar.dataset.width;
        });
      }, 200);
    });

    // Inyectar tema dinámico desde el sprite
    if (sprite) {
      ThemeEngine.applyFromImage(sprite);
    }
  }

  function hideDetail() {
    els.detailOverlay.classList.remove('detail-overlay--visible');
    document.body.style.overflow = '';
  }

  /* --------------------------------------------------------------------------
     5. PANEL DE ERROR Y ESTADO VACÍO
     -------------------------------------------------------------------------- */
  function showError(error) {
    clearResults();
    hideEmpty();
    const codeMap = {
      NETWORK_ERROR: 'ERR::NETWORK_FAILURE',
      NOT_FOUND: 'ERR::ENTITY_NOT_FOUND',
      RATE_LIMIT: 'ERR::RATE_LIMIT_EXCEEDED',
      UNKNOWN_ERROR: 'ERR::UNKNOWN_ANOMALY',
    };
    els.errorCode.textContent = codeMap[error?.type] || 'ERR::UNKNOWN_ANOMALY';
    els.errorPanel.classList.add('error-panel--visible');
  }

  function hideError() {
    els.errorPanel.classList.remove('error-panel--visible');
  }

  function showEmpty() {
    els.emptyState.style.display = 'block';
  }

  function hideEmpty() {
    els.emptyState.style.display = 'none';
  }

  /* --------------------------------------------------------------------------
     6. BOTONES DE FILTRO POR TIPO
     -------------------------------------------------------------------------- */
  function renderTypeFilters(types, onClick) {
    els.typeFilters.innerHTML = types.map(t =>
      `<button class="type-pill" data-type="${t.name}">${t.name}</button>`
    ).join('');

    els.typeFilters.addEventListener('click', (e) => {
      const pill = e.target.closest('.type-pill');
      if (!pill) return;

      const wasActive = pill.classList.contains('type-pill--active');
      els.typeFilters.querySelectorAll('.type-pill').forEach(p => p.classList.remove('type-pill--active'));

      if (!wasActive) {
        pill.classList.add('type-pill--active');
        onClick(pill.dataset.type);
      } else {
        onClick(null); // Deseleccionar: volver a destacados
      }
    });
  }

  /* --------------------------------------------------------------------------
     7. UTILIDADES
     -------------------------------------------------------------------------- */
  function clearResults() {
    els.resultsGrid.innerHTML = '';
  }

  return {
    cacheDom,
    showLoading, hideLoading,
    showSkeletons, hideSkeletons,
    renderCards,
    showDetail, hideDetail,
    showError, hideError,
    showEmpty, hideEmpty,
    renderTypeFilters,
    clearResults,
    TYPE_COLORS,
  };
})();

