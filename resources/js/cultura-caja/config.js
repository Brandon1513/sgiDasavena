/**
 * Configuración de la animación 3D de la caja Dasavena (vista /cultura).
 *
 * Aquí se ajusta todo lo que normalmente hace falta tocar:
 *   - TEXTURES: qué parte de cada imagen se usa como arte de la cara.
 *   - BOX:      proporciones físicas del empaque.
 *   - TIMING:   duración y orden de cada fase de la animación.
 *
 * Las URLs de las imágenes NO viven aquí: las pasa la vista Blade mediante
 * data-attributes (asset('images/dasavena/...')), para respetar el APP_URL.
 * Para cambiar el arte basta con sustituir los archivos de
 * public/images/dasavena/ y, si el encuadre cambia, actualizar `crop`.
 */

/**
 * Recorte en píxeles del arte dentro de cada imagen.
 * Las fotos actuales traen un margen blanco alrededor del cartón; el recorte
 * lo elimina para que la cara 3D muestre solo el empaque, sin estirarlo.
 * Si se reemplazan por el arte de impresión (sin márgenes), basta con
 * poner `crop: null` y se usará la imagen completa.
 */
export const TEXTURES = {
    front: { crop: { x: 9, y: 15, width: 490, height: 650 } },  // caja-frontal.png (511×688)
    back:  { crop: { x: 18, y: 14, width: 487, height: 644 } }, // caja-trasera.png (520×676)
};

/** Medidas en unidades de escena. width / height ≈ proporción del arte (0.755). */
export const BOX = {
    height: 3.0,
    width: 2.265,
    depth: 0.72,
    thickness: 0.014,      // grosor del cartón (bordes visibles)
    dustFlapLength: 0.30,  // solapas laterales (guardapolvos) superior e inferior
    tuckLength: 0.2,       // pestaña que entra por dentro del frente al cerrar la tapa
    hoverHeight: 0.34,     // altura a la que flota la caja sobre el piso al final
};

/**
 * Colores del cartón kraft liso (solapas, laterales, interior). Son más oscuros
 * que el kraft de las fotos porque la iluminación los aclara; están ajustados
 * para que, ya renderizados, coincidan con el kraft impreso del frente.
 */
export const COLORS = {
    kraft: '#b08e75',
    kraftInner: '#c8b093',
    kraftEdge: '#94765a',
};

/**
 * Fases de la secuencia, en milisegundos de la línea de tiempo de Anime.js.
 * La animación no corre sola: el scroll la recorre (ver SCROLL), así que
 * estos valores definen el orden y la proporción de cada movimiento dentro
 * de su fase, no la velocidad real. Las fases pueden traslaparse un poco
 * para que el movimiento sea continuo.
 */
export const TIMING = {
    intro:    { start: 0,    duration: 1100 }, // Fase 1: piezas suspendidas se juntan
    assemble: { start: 1000, duration: 2000 }, // Fase 2: laterales y trasero se pliegan
    close:    { start: 2900, duration: 2100 }, // Fase 3: solapas inferiores y superiores
    reveal:   { start: 4900, duration: 1600 }, // Fase 4: giro de presentación + ingredientes
    hero:     { start: 6300, duration: 1000 }, // Fase 5: acercamiento final
};

/**
 * Recorrido por scroll.
 *   screens:    alto de la sección en pantallas. Más pantallas = armado más
 *               lento por cada giro de la rueda del mouse.
 *   introShare: fracción inicial del scroll con la frase de bienvenida.
 *   tailShare:  fracción final con la caja terminada en reposo.
 * El resto se reparte en partes iguales: cada valor ocupa una banda, y las
 * cinco fases del armado se reparten igual, de modo que con cinco valores
 * cada valor acompaña una fase (Candidez → piezas a la vista,
 * Creatividad → ensamblaje, Compromiso → cierre, Espíritu Emprendedor →
 * presentación, Valentía → hero shot).
 */
export const SCROLL = {
    screens: 8,
    introShare: 0.07,
    tailShare: 0.06,
    smoothing: 7, // inercia del seguimiento del scroll (más alto = más inmediato)
};
