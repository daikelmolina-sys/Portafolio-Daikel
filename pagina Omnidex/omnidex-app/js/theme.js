/* ==========================================================================
   theme.js — Motor de Extracción Dinámica de Color y Variables CSS
   ==========================================================================
   Analiza la imagen (sprite) del Pokémon activo mediante un <canvas> oculto,
   extrae el color dominante no trivial, calcula su color complementario
   y actualiza en tiempo real las variables CSS (--primary-color, --accent, etc.)
   para generar una atmósfera inmersiva personalizada por criatura.
   ========================================================================== */

const ThemeEngine = (() => {
  // Canvas oculto para muestreo de píxeles
  let canvas = null;
  let ctx = null;

  /**
   * Inicializa el canvas de 64x64 píxeles si aún no existe.
   */
  function ensureCanvas() {
    if (!canvas) {
      canvas = document.createElement('canvas');
      canvas.width = 64;
      canvas.height = 64;
      ctx = canvas.getContext('2d', { willReadFrequently: true });
    }
  }

  /**
   * Extrae el color dominante promedio de una imagen mediante muestreo de píxeles.
   * Descarta píxeles transparentes, blancos puros, negros puros o desaturados (grises).
   * 
   * @param {string} imageUrl URL del sprite del Pokémon.
   * @returns {Promise<{r:number, g:number, b:number}>}
   */
  function extractDominantColor(imageUrl) {
    return new Promise((resolve) => {
      ensureCanvas();
      const img = new Image();
      img.crossOrigin = 'anonymous'; // Permitir muestreo CORS

      img.onload = () => {
        ctx.clearRect(0, 0, 64, 64);
        ctx.drawImage(img, 0, 0, 64, 64);

        let data;
        try {
          data = ctx.getImageData(0, 0, 64, 64).data;
        } catch (e) {
          // Si falla CORS o canvas protegido, retornar color violeta por defecto
          resolve({ r: 108, g: 92, b: 231 });
          return;
        }

        let rSum = 0, gSum = 0, bSum = 0, count = 0;

        // Muestrear cada 4to píxel (i += 16) para máximo rendimiento
        for (let i = 0; i < data.length; i += 16) {
          const r = data[i];
          const g = data[i + 1];
          const b = data[i + 2];
          const a = data[i + 3];

          // 1. Ignorar píxeles transparentes
          if (a < 100) continue;
          // 2. Ignorar extremos de brillo (casi negro o casi blanco)
          const brightness = (r + g + b) / 3;
          if (brightness < 20 || brightness > 235) continue;
          // 3. Ignorar tonos grises sin saturación
          const max = Math.max(r, g, b);
          const min = Math.min(r, g, b);
          if (max - min < 15) continue;

          rSum += r;
          gSum += g;
          bSum += b;
          count++;
        }

        if (count === 0) {
          resolve({ r: 108, g: 92, b: 231 }); // Color base si la imagen es neutra
          return;
        }

        resolve({
          r: Math.round(rSum / count),
          g: Math.round(gSum / count),
          b: Math.round(bSum / count),
        });
      };

      img.onerror = () => {
        resolve({ r: 108, g: 92, b: 231 });
      };

      img.src = imageUrl;
    });
  }

  /**
   * Calcula un color de acento armónico rotando el matiz (Hue) ~150 grados en HSL.
   * @param {{r:number, g:number, b:number}} rgb Color primario.
   */
  function computeAccent({ r, g, b }) {
    const hsl = rgbToHsl(r, g, b);
    hsl.h = (hsl.h + 150) % 360;
    hsl.s = Math.min(hsl.s + 15, 90);
    hsl.l = Math.max(Math.min(hsl.l, 60), 40);
    return hslToRgb(hsl.h, hsl.s, hsl.l);
  }

  /**
   * Inyecta los valores calculados en las variables CSS del :root del documento.
   */
  function applyTheme(primary, accent) {
    const root = document.documentElement;
    root.style.setProperty('--primary-color', `rgb(${primary.r}, ${primary.g}, ${primary.b})`);
    root.style.setProperty('--primary-rgb', `${primary.r}, ${primary.g}, ${primary.b}`);
    root.style.setProperty('--accent', `rgb(${accent.r}, ${accent.g}, ${accent.b})`);
    root.style.setProperty('--accent-rgb', `${accent.r}, ${accent.g}, ${accent.b}`);
    root.style.setProperty('--bg-glow', `rgba(${primary.r}, ${primary.g}, ${primary.b}, 0.15)`);
    root.style.setProperty('--border-glow', `rgba(${primary.r}, ${primary.g}, ${primary.b}, 0.35)`);
  }

  /**
   * Flujo completo: Extraer color de la imagen -> calcular acento -> inyectar estilos.
   */
  async function applyFromImage(imageUrl) {
    const primary = await extractDominantColor(imageUrl);
    const accent = computeAccent(primary);
    applyTheme(primary, accent);
    return { primary, accent };
  }

  /**
   * Restablece el tema a los colores futuristas violeta y cian por defecto.
   */
  function resetTheme() {
    applyTheme({ r: 108, g: 92, b: 231 }, { r: 0, g: 206, b: 201 });
  }

  // --- Funciones auxiliares de conversión matemática de color (RGB <-> HSL) ---
  function rgbToHsl(r, g, b) {
    r /= 255; g /= 255; b /= 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h = 0, s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
      const d = max - min;
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
      if (max === r) h = ((g - b) / d + (g < b ? 6 : 0));
      else if (max === g) h = ((b - r) / d + 2);
      else h = ((r - g) / d + 4);
      h *= 60;
    }

    return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) };
  }

  function hslToRgb(h, s, l) {
    s /= 100; l /= 100;
    const c = (1 - Math.abs(2 * l - 1)) * s;
    const x = c * (1 - Math.abs((h / 60) % 2 - 1));
    const m = l - c / 2;
    let r = 0, g = 0, b = 0;

    if (h < 60)       { r = c; g = x; }
    else if (h < 120) { r = x; g = c; }
    else if (h < 180) { g = c; b = x; }
    else if (h < 240) { g = x; b = c; }
    else if (h < 300) { r = x; b = c; }
    else              { r = c; b = x; }

    return {
      r: Math.round((r + m) * 255),
      g: Math.round((g + m) * 255),
      b: Math.round((b + m) * 255),
    };
  }

  return { extractDominantColor, applyFromImage, applyTheme, resetTheme };
})();

