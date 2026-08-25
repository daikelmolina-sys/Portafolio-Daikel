/* ==========================================================================
   tilt.js — Efecto Holográfico 3D de Inclinación con Seguimiento de Cursor
   ==========================================================================
   Calcula la posición del cursor del ratón respecto al centro de cada tarjeta,
   aplica transformaciones CSS 3D (perspective, rotateX, rotateY, scale3d)
   y actualiza variables CSS (--mouse-x, --mouse-y) para el reflejo de luz.
   ========================================================================== */

const TiltEffect = (() => {
  const MAX_TILT = 12;         // Inclinación máxima en grados
  const PERSPECTIVE = 1000;    // Profundidad de perspectiva 3D en píxeles
  const TRANSITION_SPEED = '0.15s'; // Velocidad de respuesta al mover el cursor
  const RESET_SPEED = '0.5s';  // Velocidad de suavizado al salir el cursor

  /**
   * Inicializa el listener de movimiento en el contenedor padre.
   * Utiliza delegación de eventos para soportar tarjetas agregadas dinámicamente.
   * @param {string|Element} container Contenedor padre (ej. '#resultsGrid')
   * @param {string} childSelector Selector de las tarjetas interactivas (ej. '.poke-card')
   */
  function init(container, childSelector) {
    const parent = typeof container === 'string'
      ? document.querySelector(container)
      : container;

    if (!parent) return;

    // Detectar movimiento del cursor sobre una tarjeta
    parent.addEventListener('mousemove', (e) => {
      const card = e.target.closest(childSelector);
      if (!card) return;
      handleMove(card, e);
    });

    // Resetear cuando el cursor sale del contenedor
    parent.addEventListener('mouseleave', (e) => {
      const card = e.target.closest(childSelector);
      if (card) resetCard(card);
    }, true);

    // Resetear cuando el cursor sale de una tarjeta individual
    parent.addEventListener('mouseout', (e) => {
      const card = e.target.closest(childSelector);
      if (card && !card.contains(e.relatedTarget)) {
        resetCard(card);
      }
    });
  }

  /**
   * Calcula la rotación 3D y el reflejo de luz según la posición del cursor.
   * @param {HTMLElement} card Tarjeta objetivo
   * @param {MouseEvent} e Evento del ratón
   */
  function handleMove(card, e) {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    // Normalizar coordenadas relativas al centro entre -1.0 y 1.0
    const normalX = (x - centerX) / centerX;
    const normalY = (y - centerY) / centerY;

    // Invertir eje Y para una sensación física realista de profundidad
    const rotateX = -normalY * MAX_TILT;
    const rotateY = normalX * MAX_TILT;

    // Aplicar transformación tridimensional
    card.style.transition = `transform ${TRANSITION_SPEED} ease-out`;
    card.style.transform = `
      perspective(${PERSPECTIVE}px)
      rotateX(${rotateX.toFixed(2)}deg)
      rotateY(${rotateY.toFixed(2)}deg)
      scale3d(1.03, 1.03, 1.03)
    `;

    // Actualizar variables CSS para el brillo de luz especular
    const percentX = ((x / rect.width) * 100).toFixed(1);
    const percentY = ((y / rect.height) * 100).toFixed(1);
    card.style.setProperty('--mouse-x', percentX + '%');
    card.style.setProperty('--mouse-y', percentY + '%');
  }

  /**
   * Restablece la tarjeta a su posición plana inicial con animación suave.
   * @param {HTMLElement} card Tarjeta objetivo
   */
  function resetCard(card) {
    card.style.transition = `transform ${RESET_SPEED} ease`;
    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1,1,1)';
  }

  return { init };
})();

