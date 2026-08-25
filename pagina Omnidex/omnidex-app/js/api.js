/* ==========================================================================
   api.js — Módulo de Comunicación con PokéAPI v2
   ==========================================================================
   Gestiona todas las peticiones HTTP a la API pública de Pokémon, incluyendo:
   - Sistema de caché en memoria (Map) para evitar peticiones repetidas.
   - Manejo tipado de errores (Red, 404 No Encontrado, 429 Límite de Peticiones).
   - Búsqueda por nombre / ID, listado por tipos y Pokémon destacados.
   ========================================================================== */

const PokeAPI = (() => {
  // URL base de la API REST pública
  const BASE = 'https://pokeapi.co/api/v2';
  // Caché en memoria para almacenar respuestas previas y acelerar la navegación
  const cache = new Map();

  // Tipos de errores estandarizados para la UI
  const ErrorType = {
    NETWORK:   'NETWORK_ERROR',
    NOT_FOUND: 'NOT_FOUND',
    RATE_LIMIT:'RATE_LIMIT',
    UNKNOWN:   'UNKNOWN_ERROR',
  };

  /**
   * Petición HTTP genérica con soporte de caché y captura de errores.
   * @param {string} url Endpoint completo a consultar.
   * @returns {Promise<Object>} Datos en formato JSON.
   */
  async function request(url) {
    if (cache.has(url)) return cache.get(url);

    try {
      const res = await fetch(url);
      if (!res.ok) {
        if (res.status === 404) throw { type: ErrorType.NOT_FOUND, status: 404, message: 'Recurso no encontrado.' };
        if (res.status === 429) throw { type: ErrorType.RATE_LIMIT, status: 429, message: 'Demasiadas solicitudes. Espera un momento.' };
        throw { type: ErrorType.UNKNOWN, status: res.status, message: `HTTP ${res.status}` };
      }
      const data = await res.json();
      cache.set(url, data); // Guardar en caché
      return data;
    } catch (err) {
      if (err.type) throw err;
      throw { type: ErrorType.NETWORK, status: 0, message: err.message || 'Error de red al conectar con el servidor.' };
    }
  }

  /**
   * Busca un Pokémon específico por su nombre o su ID en la Pokédex.
   * @param {string|number} query Nombre (ej. 'pikachu') o ID (ej. 25).
   */
  async function searchPokemon(query) {
    const q = String(query).toLowerCase().trim();
    if (!q) return null;
    return request(`${BASE}/pokemon/${encodeURIComponent(q)}`);
  }

  /**
   * Obtiene la ficha técnica completa de un Pokémon (stats, tipos, sprites, habilidades).
   * @param {string|number} idOrName Identificador o nombre del Pokémon.
   */
  async function getPokemonDetails(idOrName) {
    return request(`${BASE}/pokemon/${idOrName}`);
  }

  /**
   * Obtiene los datos de la especie (descripción de la Pokédex, hábitat, color).
   * @param {string|number} idOrName Identificador o nombre del Pokémon.
   */
  async function getPokemonSpecies(idOrName) {
    return request(`${BASE}/pokemon-species/${idOrName}`);
  }

  /**
   * Obtiene la lista de todos los tipos elementales válidos (fuego, agua, etc).
   */
  async function getTypeList() {
    const data = await request(`${BASE}/type`);
    // Excluir tipos técnicos no jugables convencionales
    return data.results.filter(t => !['unknown', 'shadow', 'stellar'].includes(t.name));
  }

  /**
   * Obtiene todos los Pokémon pertenecientes a un tipo elemental específico.
   * @param {string} type Nombre del tipo (ej. 'fire', 'water').
   */
  async function getPokemonByType(type) {
    const data = await request(`${BASE}/type/${type}`);
    return data.pokemon.map(p => p.pokemon);
  }

  /**
   * Obtiene una página paginada de Pokémon para la vista "Explorar".
   * Resuelve los detalles completos de cada Pokémon en paralelo con Promise.all.
   * @param {number} offset Índice de inicio.
   * @param {number} limit Cantidad máxima por página.
   */
  async function getPokemonList(offset = 0, limit = 20) {
    const data = await request(`${BASE}/pokemon?offset=${offset}&limit=${limit}`);
    const details = await Promise.all(
      data.results.map(p => getPokemonDetails(p.name))
    );
    return { results: details, count: data.count, next: data.next };
  }

  /**
   * Obtiene un conjunto de Pokémon aleatorios destacados (Gens 1 a 5).
   * @param {number} count Cantidad de criaturas a seleccionar.
   */
  async function getFeatured(count = 12) {
    const ids = new Set();
    while (ids.size < count) {
      ids.add(Math.floor(Math.random() * 649) + 1);
    }
    return Promise.all([...ids].map(id => getPokemonDetails(id)));
  }

  // Métodos y constantes públicas del módulo
  return {
    searchPokemon,
    getPokemonDetails,
    getPokemonSpecies,
    getTypeList,
    getPokemonByType,
    getPokemonList,
    getFeatured,
    ErrorType,
  };
})();

