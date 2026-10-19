/**
 * Entrada Vite de la animación 3D de la caja Dasavena (vista /cultura).
 * Se monta sobre cualquier elemento con [data-caja3d]; ver
 * resources/views/cultura/partials/caja-3d.blade.php.
 *
 * La caja se arma con el scroll: la sección mide SCROLL.screens pantallas,
 * el escenario queda fijo (sticky) y el avance del scroll recorre la línea
 * de tiempo de Anime.js. Al mismo tiempo aparecen los valores, uno por banda.
 */
import '../../css/cultura-caja.css';
import { animate } from 'animejs';
import { MathUtils } from 'three';
import { SCROLL } from './config.js';
import { createScene, frameCamera } from './scene.js';
import { loadTextures, createBox, createBoxState, applyBoxState } from './box.js';
import { createIngredients } from './ingredients.js';
import { buildTimeline, createCameraState, phaseKeyTimes } from './timeline.js';

/*
 * Encuadres en unidades de escena: plantilla extendida (inicio) y caja
 * terminada (hero). `region` reserva una parte de la pantalla al producto:
 * en escritorio el 58 % derecho (texto a la izquierda), en móvil el 68 %
 * superior (texto abajo). Debe coincidir con cultura-caja.css.
 */
const FRAMING = {
    side:  { net: { width: 6.3, height: 5.0 }, hero: { width: 3.9, height: 4.8 }, region: { x: 0.58, y: 1 }, targetY: -0.1 },
    stack: { net: { width: 7.2, height: 5.0 }, hero: { width: 4.2, height: 4.6 }, region: { x: 1, y: 0.68 }, targetY: -0.1 },
};
const SIDE_LAYOUT_QUERY = '(min-width: 900px) and (min-aspect-ratio: 1/1)';

function webglAvailable() {
    try {
        const c = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
    } catch {
        return false;
    }
}

const smoothstep = (a, b, x) => {
    const t = MathUtils.clamp((x - a) / (b - a), 0, 1);
    return t * t * (3 - 2 * t);
};

export function mountCaja3D(container) {
    const stage = container.querySelector('[data-caja3d-stage]');
    const slots = [...container.querySelectorAll('[data-caja3d-slot]')]; // 0 = frase, 1..N = valores
    const railDots = [...container.querySelectorAll('[data-caja3d-goto]')];
    const bar = container.querySelector('[data-caja3d-progress]');
    const hint = container.querySelector('[data-caja3d-hint]');

    if (!stage || !webglAvailable()) {
        container.classList.add('is-fallback');
        return () => {};
    }

    // A partir de aquí la sección pasa a modo "scroll": alta y con escenario fijo.
    container.classList.add('is-live');
    container.style.setProperty('--caja3d-screens', SCROLL.screens);

    const reducedQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const sideQuery = window.matchMedia(SIDE_LAYOUT_QUERY);
    let reduced = reducedQuery.matches;

    const ctx = createScene(stage);
    const { renderer, scene, camera, contact, contactPivot } = ctx;
    const canvas = renderer.domElement;
    canvas.style.opacity = '0';
    const viewport = { width: stage.clientWidth, height: stage.clientHeight };

    const boxState = createBoxState();
    const camState = createCameraState();
    const keyTimes = phaseKeyTimes();
    const valueCount = slots.length - 1;
    let box = null;
    let ingredients = null;
    let timeline = null;

    let rafId = 0;
    let lastNow = 0;
    let clock = 0;      // segundos de movimiento "idle" (flotación, deriva de ingredientes)
    let idle = 0;       // 0..1, entra suavemente cuando la caja está terminada
    let target = 0;     // avance del scroll (0..1)
    let progress = 0;   // avance suavizado que se dibuja
    let inView = false;
    let dirty = true;
    let activeSlot = -1;
    const pointer = { x: 0, y: 0, tx: 0, ty: 0 };

    /* ── Scroll → avance ── */
    function readScroll() {
        const rect = container.getBoundingClientRect();
        const scrollable = rect.height - window.innerHeight;
        target = scrollable > 0 ? MathUtils.clamp(-rect.top / scrollable, 0, 1) : 0;
    }

    const valuesStart = SCROLL.introShare;
    const valuesEnd = 1 - SCROLL.tailShare;

    /** Avance del scroll → milisegundos de la línea de tiempo (tramos por fase). */
    function progressToTime(p) {
        if (p <= valuesStart) return MathUtils.lerp(keyTimes[0], keyTimes[1], p / valuesStart);
        if (p >= valuesEnd) return keyTimes[keyTimes.length - 1];
        const phases = keyTimes.length - 2;
        const q = ((p - valuesStart) / (valuesEnd - valuesStart)) * phases;
        const i = Math.min(Math.floor(q), phases - 1);
        return MathUtils.lerp(keyTimes[i + 1], keyTimes[i + 2], q - i);
    }

    /** Banda de scroll de cada bloque de texto: [inicio, fin]. */
    function slotBand(i) {
        if (i === 0) return [0, valuesStart];
        const w = (valuesEnd - valuesStart) / valueCount;
        return [valuesStart + (i - 1) * w, valuesStart + i * w];
    }

    /** Opacidad y desplazamiento de cada texto; entre uno y otro hay una pausa sin traslape. */
    function updateSlots(p) {
        let best = 0;
        let bestAlpha = -1;
        slots.forEach((el, i) => {
            const [a, b] = slotBand(i);
            const u = (p - a) / (b - a);
            const isFirst = i === 0;
            const isLast = i === slots.length - 1;
            const fadeIn = isFirst ? 1 : smoothstep(0.04, 0.22, u);
            const fadeOut = isLast ? 1 : 1 - smoothstep(isFirst ? 0.45 : 0.78, isFirst ? 0.95 : 0.96, u);
            const alpha = u < 0 ? 0 : Math.min(fadeIn, fadeOut);
            const shift = u < 0.5 ? (1 - fadeIn) * 28 : -(1 - fadeOut) * 28;
            el.style.opacity = alpha.toFixed(3);
            el.style.transform = `translate3d(0, ${shift.toFixed(1)}px, 0)`;
            el.classList.toggle('is-visible', alpha > 0.5);
            if (alpha > bestAlpha) { bestAlpha = alpha; best = i; }
        });
        if (best !== activeSlot) {
            activeSlot = best;
            railDots.forEach((dot, i) => {
                const on = i + 1 === best;
                dot.classList.toggle('is-active', on);
                on ? dot.setAttribute('aria-current', 'step') : dot.removeAttribute('aria-current');
            });
        }
        if (bar) bar.style.transform = `scaleX(${p.toFixed(4)})`;
        if (hint) hint.style.opacity = String(1 - smoothstep(0, 0.025, p));
    }

    /* ── Render ── */
    function renderFrame() {
        if (!box) return;
        const motion = !reduced;
        const float = Math.sin(clock * 1.1) * 0.045 * idle;
        const sway = Math.sin(clock * 0.5) * 0.07 * idle;

        applyBoxState(box, boxState, { float, sway });
        ingredients.update(camState.ingredients, clock, motion);

        contactPivot.rotation.y = boxState.yaw + sway;
        const height = Math.max(0, boxState.lift + float);
        contact.material.opacity = boxState.shadow * MathUtils.clamp(0.9 - height * 0.9, 0.25, 0.9);

        const framing = sideQuery.matches ? FRAMING.side : FRAMING.stack;
        const fit = camState.fit;
        frameCamera(camera, {
            azimuth: camState.azimuth + pointer.x * 0.12 * idle,
            elevation: camState.elevation + pointer.y * 0.05 * idle,
            fitWidth: MathUtils.lerp(framing.net.width, framing.hero.width, fit),
            fitHeight: MathUtils.lerp(framing.net.height, framing.hero.height, fit),
            zoom: camState.zoom,
            targetY: boxState.lift * 0.8 + framing.targetY * fit,
            region: framing.region,
            viewport,
        });

        renderer.render(scene, camera);
    }

    /** Un único ciclo de render; solo dibuja cuando algo cambió. */
    function loop(now) {
        rafId = requestAnimationFrame(loop);
        const elapsed = (now - (lastNow || now)) / 1000;
        const dt = Math.min(elapsed, 0.1);
        lastNow = now;

        const motion = !reduced;
        const before = progress;
        // El seguimiento del scroll usa el tiempo real (hasta 0.5 s) para que
        // en equipos lentos la caja no se quede atrás de la página.
        const follow = 1 - Math.exp(-Math.min(elapsed, 0.5) * SCROLL.smoothing);
        progress = reduced ? target : progress + (target - progress) * follow;
        if (Math.abs(target - progress) < 0.0002) progress = target;
        const scrolling = progress !== before;

        const finished = progress >= valuesEnd - 0.01;
        const idleTarget = finished && motion ? 1 : 0;
        const idleMoving = Math.abs(idleTarget - idle) > 0.001;
        const pointerMoving = Math.abs(pointer.tx - pointer.x) + Math.abs(pointer.ty - pointer.y) > 0.0005;

        if (motion) clock += dt;
        idle += (idleTarget - idle) * Math.min(1, dt * 1.5);
        pointer.x += (pointer.tx - pointer.x) * Math.min(1, dt * 4);
        pointer.y += (pointer.ty - pointer.y) * Math.min(1, dt * 4);

        if (scrolling || dirty) {
            timeline?.seek(progressToTime(progress), true);
            updateSlots(progress);
        }
        if (scrolling || dirty || idleMoving || pointerMoving || (motion && (finished || camState.ingredients > 0))) {
            dirty = false;
            renderFrame();
        }
    }

    function startLoop() {
        if (rafId) return;
        lastNow = 0;
        rafId = requestAnimationFrame(loop);
    }

    function stopLoop() {
        cancelAnimationFrame(rafId);
        rafId = 0;
    }

    /* ── Eventos ── */
    const onScroll = () => { readScroll(); };
    const onResize = () => {
        viewport.width = stage.clientWidth;
        viewport.height = stage.clientHeight;
        ctx.setSize(viewport.width, viewport.height);
        readScroll();
        dirty = true;
    };
    const onVisibility = () => syncActive();
    const onReducedChange = (e) => { reduced = e.matches; dirty = true; };
    const onLayoutChange = () => { dirty = true; };

    function onPointerMove(e) {
        const r = stage.getBoundingClientRect();
        pointer.tx = ((e.clientX - r.left) / r.width - 0.5) * 2;
        pointer.ty = ((e.clientY - r.top) / r.height - 0.5) * 2;
    }
    function onPointerLeave() {
        pointer.tx = 0;
        pointer.ty = 0;
    }

    /** Navegación del riel: lleva el scroll al centro de la banda de ese valor. */
    function onRailClick(e) {
        const i = Number(e.currentTarget.dataset.caja3dGoto);
        const [a, b] = slotBand(i);
        const rect = container.getBoundingClientRect();
        const scrollable = rect.height - window.innerHeight;
        const top = window.scrollY + rect.top + scrollable * ((a + b) / 2);
        window.scrollTo({ top, behavior: reduced ? 'auto' : 'smooth' });
    }

    /* ── Visibilidad: pestaña oculta o sección fuera de pantalla ── */
    function syncActive() {
        if (inView && !document.hidden) {
            readScroll();
            dirty = true;
            startLoop();
        } else {
            stopLoop();
        }
    }

    const io = new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        syncActive();
    });
    const ro = new ResizeObserver(onResize);

    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('visibilitychange', onVisibility);
    reducedQuery.addEventListener?.('change', onReducedChange);
    sideQuery.addEventListener?.('change', onLayoutChange);
    railDots.forEach((dot) => dot.addEventListener('click', onRailClick));
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        stage.addEventListener('pointermove', onPointerMove);
        stage.addEventListener('pointerleave', onPointerLeave);
    }
    ro.observe(stage);
    io.observe(container);
    readScroll();
    progress = target; // si se recarga a media página, arranca donde quedó el scroll
    updateSlots(progress);

    /* ── Carga de recursos ── */
    let disposed = false;
    loadTextures(renderer, {
        front: container.dataset.frontSrc,
        back: container.dataset.backSrc,
    }).then((textures) => {
        if (disposed) {
            textures.front?.dispose();
            textures.back?.dispose();
            return;
        }
        box = createBox(textures);
        scene.add(box.root);
        ingredients = createIngredients(ctx.floorY);
        scene.add(ingredients.group);

        timeline = buildTimeline(boxState, camState);
        timeline.seek(progressToTime(progress), true);
        container.classList.add('is-ready');
        animate(canvas, { opacity: { from: 0, to: 1 }, duration: reduced ? 0 : 700, ease: 'outQuad' });
        dirty = true;
    });

    /** Libera geometrías, materiales, texturas, observadores y el contexto WebGL. */
    function disposeScene() {
        if (disposed) return;
        disposed = true;
        stopLoop();
        timeline?.cancel();
        io.disconnect();
        ro.disconnect();
        window.removeEventListener('scroll', onScroll);
        document.removeEventListener('visibilitychange', onVisibility);
        reducedQuery.removeEventListener?.('change', onReducedChange);
        sideQuery.removeEventListener?.('change', onLayoutChange);
        railDots.forEach((dot) => dot.removeEventListener('click', onRailClick));
        stage.removeEventListener('pointermove', onPointerMove);
        stage.removeEventListener('pointerleave', onPointerLeave);
        box?.dispose();
        ingredients?.dispose();
        ctx.dispose();
    }

    return disposeScene;
}

const instances = [];
function init() {
    document.querySelectorAll('[data-caja3d]').forEach((el) => instances.push(mountCaja3D(el)));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}

// Al salir de la página (sin bfcache) se libera el contexto WebGL.
window.addEventListener('pagehide', (e) => {
    if (e.persisted) return;
    instances.splice(0).forEach((dispose) => dispose());
});
