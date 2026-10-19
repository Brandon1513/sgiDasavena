import { createTimeline } from 'animejs';
import { TIMING } from './config.js';

/**
 * Orquestación de la secuencia con Anime.js.
 *
 * Anime.js no toca Three.js directamente: interpola números en dos objetos
 * de estado (`box` y `cam`) y el ciclo de render de main.js los aplica a
 * pivotes, cámara e ingredientes. La línea de tiempo nunca se reproduce
 * sola: main.js la recorre con seek() según el scroll, así que funciona
 * igual hacia adelante (se arma) que hacia atrás (se desarma).
 *
 * Las posiciones son absolutas en ms (ver TIMING en config.js). Cada
 * propiedad tiene tramos que no se traslapan.
 */

/** Agrega una interpolación from → to de una propiedad en un momento dado. */
function tween(tl, target, prop, from, to, at, duration, ease = 'inOutCubic') {
    tl.add(target, { [prop]: { from, to }, duration: Math.round(duration), ease }, Math.round(at));
}

/** Estado inicial de la cámara (ver frameCamera en scene.js). */
export function createCameraState() {
    return {
        azimuth: 0.5,    // perspectiva de tres cuartos
        elevation: 0.2,
        fit: 0,          // 0 = encuadre de la plantilla extendida, 1 = encuadre de la caja
        zoom: 1.04,      // < 1 acerca la cámara
        ingredients: 0,  // aparición de ingredientes (0..1)
    };
}

/** Fase 1 · Presentación: la plantilla extendida y sus piezas suspendidas se juntan. */
export function unfoldBox(tl, box) {
    const { start: s, duration: d } = TIMING.intro;
    tween(tl, box, 'explode', 1, 0, s, d, 'inOutQuad');
    tween(tl, box, 'tilt', -0.85, -0.6, s, d, 'inOutSine');
    tween(tl, box, 'lift', 0.75, 0.6, s, d, 'inOutSine');
}

/** Fase 2 · Ensamblaje: laterales y trasero giran desde sus líneas de doblez. */
export function assembleBox(tl, box, cam) {
    const { start: s, duration: d } = TIMING.assemble;
    tween(tl, box, 'sideL', 0, 1, s, d * 0.45);
    tween(tl, box, 'sideR', 0, 1, s + d * 0.08, d * 0.45);
    tween(tl, box, 'back', 0, 1, s + d * 0.38, d * 0.5);
    tween(tl, box, 'netShift', 1, 0, s, d * 0.85);
    tween(tl, box, 'yaw', 0, 0.75, s, d, 'inOutSine');
    tween(tl, cam, 'fit', 0, 1, s, d * 0.95);
    tween(tl, cam, 'zoom', 1.04, 1.18, s, d); // margen para la caja inclinada durante el cierre
    tween(tl, cam, 'elevation', 0.2, 0.05, s, d * 0.95, 'inOutSine');
}

/** Fase 3 · Cierre: solapas inferiores, luego superiores, en secuencia. */
export function closeFlaps(tl, box, cam) {
    const { start: s, duration: d } = TIMING.close;
    // Inferiores: la caja está inclinada hacia atrás, la base mira a cámara.
    tween(tl, box, 'dustBotL', 0, 1, s, d * 0.22);
    tween(tl, box, 'dustBotR', 0, 1, s + d * 0.05, d * 0.22);
    tween(tl, box, 'bottom', 0, 1, s + d * 0.17, d * 0.28);
    // Se endereza y se inclina hacia la cámara para mostrar la tapa.
    tween(tl, box, 'tilt', -0.6, 0.28, s + d * 0.38, d * 0.38);
    tween(tl, cam, 'elevation', 0.05, 0.32, s + d * 0.38, d * 0.38);
    tween(tl, box, 'yaw', 0.75, 3.5, s + d * 0.05, d * 0.9, 'inOutSine');
    // Superiores: guardapolvos primero, después la tapa; la pestaña se dobla
    // mientras la tapa baja para entrar por dentro del frente sin chocar.
    tween(tl, box, 'dustTopL', 0, 1, s + d * 0.5, d * 0.2);
    tween(tl, box, 'dustTopR', 0, 1, s + d * 0.55, d * 0.2);
    tween(tl, box, 'lid', 0, 1, s + d * 0.64, d * 0.34);
    tween(tl, box, 'tuck', 0, 1, s + d * 0.64, d * 0.17, 'outQuad');
}

/** Fase 4 · Presentación de marca: giro suave hasta dejar el frente a cámara. */
export function revealProduct(tl, box, cam) {
    const { start: s, duration: d } = TIMING.reveal;
    // 2π + 0.12: termina de dar la vuelta con el frente ligeramente en tres cuartos.
    tween(tl, box, 'yaw', 3.5, Math.PI * 2 + 0.12, s, d, 'inOutCubic');
    tween(tl, box, 'tilt', 0.28, 0, s, d * 0.65);
    tween(tl, box, 'lift', 0.6, 0.12, s, d * 0.875);
    tween(tl, box, 'shadow', 0, 1, s, d * 0.9, 'inOutSine');
    tween(tl, cam, 'elevation', 0.32, 0.12, s, d * 0.875);
    tween(tl, cam, 'azimuth', 0.5, 0.42, s, d);
    tween(tl, cam, 'ingredients', 0, 1, s + d * 0.2, d * 0.8, 'linear');
}

/** Fase 5 · Hero shot: la caja se asienta y la cámara se acerca un poco. */
export function heroShot(tl, box, cam) {
    const { start: s, duration: d } = TIMING.hero;
    tween(tl, box, 'lift', 0.12, 0, s, d, 'outCubic');
    tween(tl, cam, 'zoom', 1.18, 0.92, s, d, 'inOutCubic');
}

/**
 * Inicio de cada fase en la línea de tiempo: [inicio, fin de la presentación,
 * ensamblaje, cierre, giro, hero, final]. main.js reparte el scroll entre
 * estos tramos. El primer tramo (0 → 300 ms) acompaña la frase de entrada.
 */
export function phaseKeyTimes() {
    const end = TIMING.hero.start + TIMING.hero.duration;
    return [0, 300, TIMING.assemble.start, TIMING.close.start, TIMING.reveal.start, TIMING.hero.start, end];
}

/** Construye la secuencia completa en pausa; main.js la recorre con seek(). */
export function buildTimeline(box, cam) {
    const tl = createTimeline({ autoplay: false });
    unfoldBox(tl, box);
    assembleBox(tl, box, cam);
    closeFlaps(tl, box, cam);
    revealProduct(tl, box, cam);
    heroShot(tl, box, cam);
    return tl;
}

/** Devuelve la caja a la plantilla extendida (inicio de la secuencia). */
export function resetAnimation(tl) {
    tl.seek(0);
}
