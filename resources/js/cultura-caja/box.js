import * as THREE from 'three';
import { BOX, COLORS, TEXTURES } from './config.js';

const HALF_PI = Math.PI / 2;

/* ─────────────────────────────────────────────────────────────
 * Texturas
 * ───────────────────────────────────────────────────────────── */

/** Generador pseudoaleatorio determinista: el cartón se ve igual en cada carga. */
function mulberry32(seed) {
    return () => {
        seed |= 0; seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/** Cartón kraft procedural (fibras + moteado) para las caras sin arte impreso. */
function createKraftTexture(baseColor, seed) {
    const size = 512;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');
    const rand = mulberry32(seed);

    ctx.fillStyle = baseColor;
    ctx.fillRect(0, 0, size, size);

    // Moteado fino
    for (let i = 0; i < 9000; i++) {
        const light = rand() > 0.5;
        ctx.fillStyle = light ? `rgba(255,240,220,${0.05 + rand() * 0.08})` : `rgba(70,40,20,${0.04 + rand() * 0.08})`;
        ctx.fillRect(rand() * size, rand() * size, 1 + rand() * 1.5, 1 + rand() * 1.5);
    }

    // Fibras cortas; se dibujan también desplazadas para que la textura repita sin costuras
    ctx.lineCap = 'round';
    for (let i = 0; i < 1400; i++) {
        const x = rand() * size;
        const y = rand() * size;
        const len = 4 + rand() * 16;
        const ang = (rand() - 0.5) * 0.9;
        const dark = rand() > 0.45;
        ctx.strokeStyle = dark ? `rgba(80,48,24,${0.05 + rand() * 0.1})` : `rgba(255,236,210,${0.03 + rand() * 0.06})`;
        ctx.lineWidth = 0.6 + rand() * 0.8;
        for (const [ox, oy] of [[0, 0], [-size, 0], [0, -size], [-size, -size]]) {
            ctx.beginPath();
            ctx.moveTo(x + ox, y + oy);
            ctx.lineTo(x + ox + Math.cos(ang) * len, y + oy + Math.sin(ang) * len);
            ctx.stroke();
        }
    }

    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.anisotropy = 8; // Three lo limita al máximo que soporte la tarjeta
    return tex;
}

function loadImage(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.decoding = 'async';
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error(`No se pudo cargar la textura: ${url}`));
        img.src = url;
    });
}

/**
 * Enfoque (unsharp mask): resta un desenfoque de caja separable para
 * realzar bordes de letras y logotipo. Solo RGB; el alfa no se toca.
 */
function sharpen(ctx, w, h, radius, amount) {
    const image = ctx.getImageData(0, 0, w, h);
    const px = image.data;
    const tmp = new Float32Array(w * h * 3);
    const blur = new Float32Array(w * h * 3);
    const span = radius * 2 + 1;

    for (let y = 0; y < h; y++) { // horizontal
        for (let c = 0; c < 3; c++) {
            let sum = 0;
            for (let k = -radius; k <= radius; k++) sum += px[(y * w + Math.min(w - 1, Math.max(0, k))) * 4 + c];
            for (let x = 0; x < w; x++) {
                tmp[(y * w + x) * 3 + c] = sum / span;
                const add = Math.min(w - 1, x + radius + 1);
                const sub = Math.max(0, x - radius);
                sum += px[(y * w + add) * 4 + c] - px[(y * w + sub) * 4 + c];
            }
        }
    }
    for (let x = 0; x < w; x++) { // vertical
        for (let c = 0; c < 3; c++) {
            let sum = 0;
            for (let k = -radius; k <= radius; k++) sum += tmp[(Math.min(h - 1, Math.max(0, k)) * w + x) * 3 + c];
            for (let y = 0; y < h; y++) {
                blur[(y * w + x) * 3 + c] = sum / span;
                const add = Math.min(h - 1, y + radius + 1);
                const sub = Math.max(0, y - radius);
                sum += tmp[(add * w + x) * 3 + c] - tmp[(sub * w + x) * 3 + c];
            }
        }
    }
    for (let i = 0, j = 0; i < px.length; i += 4, j += 3) {
        for (let c = 0; c < 3; c++) {
            const v = px[i + c];
            px[i + c] = v + amount * (v - blur[j + c]); // Uint8ClampedArray recorta a 0..255
        }
    }
    ctx.putImageData(image, 0, 0);
}

/**
 * Prepara el arte en alta definición: recorta el margen blanco de la foto,
 * lo escala con suavizado de alta calidad hasta ART_HEIGHT px de alto y le
 * aplica enfoque. No inventa detalle que la foto no tiene, pero evita que
 * la GPU tenga que ampliar una imagen chica (lo que la vuelve borrosa) y
 * deja el texto del empaque más nítido.
 */
const ART_HEIGHT = 1600;
function prepareArt(img, crop, maxSize) {
    const src = crop ?? { x: 0, y: 0, width: img.naturalWidth, height: img.naturalHeight };
    const height = Math.min(ART_HEIGHT, maxSize);
    const width = Math.round(height * (src.width / src.height));
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, src.x, src.y, src.width, src.height, 0, 0, width, height);
    const scale = height / src.height;
    if (scale > 1.2) sharpen(ctx, width, height, Math.max(1, Math.round(scale * 0.8)), 0.75);
    return canvas;
}

/**
 * Carga el arte frontal y trasero. Si una imagen falla, esa cara queda en
 * kraft liso y se avisa en consola en lugar de romper la experiencia.
 */
export async function loadTextures(renderer, urls) {
    const maxAniso = renderer.capabilities.getMaxAnisotropy();
    const maxSize = renderer.capabilities.maxTextureSize;
    const result = {};

    await Promise.all(['front', 'back'].map(async (face) => {
        if (!urls[face]) return;
        try {
            const img = await loadImage(urls[face]);
            const tex = new THREE.CanvasTexture(prepareArt(img, TEXTURES[face]?.crop, maxSize));
            tex.colorSpace = THREE.SRGBColorSpace;
            tex.anisotropy = maxAniso; // nítido aunque la cara se vea en ángulo
            tex.generateMipmaps = true;
            tex.minFilter = THREE.LinearMipmapLinearFilter;
            tex.magFilter = THREE.LinearFilter;
            result[face] = tex;
        } catch (err) {
            console.warn('[caja3d]', err.message);
        }
    }));

    return result;
}

/* ─────────────────────────────────────────────────────────────
 * Geometría
 * ───────────────────────────────────────────────────────────── */

/**
 * Lámina de cartón de w×h con grosor t. `ax`/`ay` indican hacia dónde crece
 * desde el pivote: 'c' centrada, '+' hacia el eje positivo, '-' hacia el
 * negativo. La cara impresa queda en z = 0 mirando a +z; el grosor hacia -z
 * (el interior de la caja).
 */
function sheetGeometry(w, h, t, ax, ay, { offsetY = 0, uvScale = null } = {}) {
    const geo = new THREE.BoxGeometry(w, h, t);
    const dx = ax === '+' ? w / 2 : ax === '-' ? -w / 2 : 0;
    const dy = (ay === '+' ? h / 2 : ay === '-' ? -h / 2 : 0) + offsetY;
    geo.translate(dx, dy, -t / 2);

    // En las caras kraft se escala el UV según el tamaño real para que la fibra no se estire.
    if (uvScale) {
        const uv = geo.attributes.uv;
        for (let i = 0; i < uv.count; i++) uv.setXY(i, uv.getX(i) * w / uvScale, uv.getY(i) * h / uvScale);
    }
    return geo;
}

/**
 * Construye la caja como plantilla (troquel) jerárquica:
 *
 *   frente (raíz) ─┬─ lateral izq ── guardapolvos sup/inf izq
 *                  ├─ lateral der ─┬─ guardapolvos sup/inf der
 *                  │               └─ trasero ── tapa ── pestaña
 *                  └─ solapa inferior
 *
 * Cada pieza es: pivot (en la línea de doblez, aquí se aplica el plegado)
 *   → slot (desplazamiento de "pieza suspendida") → mesh.
 * Con todo en 0 la plantilla está extendida en el plano XY.
 */
export function createBox(textures) {
    const { width: W, height: H, depth: D, thickness: t, dustFlapLength: L, tuckLength } = BOX;
    const gap = 0.002;
    const dustW = D * 0.86;
    const tuckW = W - 2 * (L + t + 0.06); // más angosta que el hueco entre guardapolvos
    const kraftScale = 1.6;

    const disposables = [];
    const track = (obj) => { disposables.push(obj); return obj; };

    const kraftTex = track(createKraftTexture(COLORS.kraft, 7));
    const innerTex = track(createKraftTexture(COLORS.kraftInner, 13));

    const kraft = track(new THREE.MeshStandardMaterial({ color: 0xffffff, map: kraftTex, bumpMap: kraftTex, bumpScale: 0.6, roughness: 0.93, metalness: 0 }));
    const inner = track(new THREE.MeshStandardMaterial({ color: 0xffffff, map: innerTex, roughness: 0.97, metalness: 0 }));
    const edge = track(new THREE.MeshStandardMaterial({ color: COLORS.kraftEdge, roughness: 1, metalness: 0 }));

    // El arte se deja sin bump para que logotipo y textos se lean nítidos.
    const printMaterial = (tex) => tex
        ? track(new THREE.MeshStandardMaterial({ map: tex, roughness: 0.78, metalness: 0 }))
        : kraft;
    const frontMat = printMaterial(textures.front);
    const backMat = printMaterial(textures.back);

    // Orden de grupos de BoxGeometry: +x, -x, +y, -y, +z (exterior), -z (interior)
    const mats = (outer) => [edge, edge, edge, edge, outer, inner];

    const root = new THREE.Group();     // gira/flota la caja completa
    const assembly = new THREE.Group(); // desplazamiento para centrar la plantilla extendida
    root.add(assembly);

    const parts = {};
    function addPart(name, parent, position, geometry, materials) {
        const pivot = new THREE.Group();
        pivot.name = name;
        pivot.position.copy(position);
        const slot = new THREE.Group();
        const mesh = new THREE.Mesh(track(geometry), materials);
        mesh.castShadow = true;
        slot.add(mesh);
        pivot.add(slot);
        (parent ? parent.slot : assembly).add(pivot);
        parts[name] = { pivot, slot, mesh };
        return parts[name];
    }
    const v = (x, y, z = 0) => new THREE.Vector3(x, y, z);
    const sheet = (w, h, ax, ay, opts) => sheetGeometry(w, h, t, ax, ay, opts);

    // ── Paneles principales ──
    const front = addPart('front', null, v(0, 0, D / 2), sheet(W, H, 'c', 'c'), mats(frontMat));
    const sideL = addPart('sideL', front, v(-W / 2, 0), sheet(D, H, '-', 'c', { uvScale: kraftScale }), mats(kraft));
    const sideR = addPart('sideR', front, v(W / 2, 0), sheet(D, H, '+', 'c', { uvScale: kraftScale }), mats(kraft));
    const back = addPart('back', sideR, v(D, 0), sheet(W, H, '+', 'c'), mats(backMat));

    // ── Solapas superiores ──
    // Los guardapolvos quedan un grosor por debajo de la tapa para no intersectarse.
    const dustOpts = (sign) => ({ offsetY: sign * (t + gap), uvScale: kraftScale });
    addPart('dustTopL', sideL, v(-D / 2, H / 2 - t - gap), sheet(dustW, L, 'c', '+', dustOpts(1)), mats(kraft));
    addPart('dustTopR', sideR, v(D / 2, H / 2 - t - gap), sheet(dustW, L, 'c', '+', dustOpts(1)), mats(kraft));
    const lid = addPart('lid', back, v(W / 2, H / 2), sheet(W, D - t, 'c', '+', { uvScale: kraftScale }), mats(kraft));
    addPart('tuck', lid, v(0, D - t), sheet(tuckW, tuckLength, 'c', '+', { uvScale: kraftScale }), mats(kraft));

    // ── Solapas inferiores ──
    addPart('dustBotL', sideL, v(-D / 2, -H / 2 + t + gap), sheet(dustW, L, 'c', '-', dustOpts(-1)), mats(kraft));
    addPart('dustBotR', sideR, v(D / 2, -H / 2 + t + gap), sheet(dustW, L, 'c', '-', dustOpts(-1)), mats(kraft));
    addPart('bottom', front, v(0, -H / 2), sheet(W, D - t, 'c', '-', { uvScale: kraftScale }), mats(kraft));

    // Desplazamientos de "pieza suspendida" (fase 1), deterministas.
    const rand = mulberry32(42);
    const explode = {};
    for (const name of Object.keys(parts)) {
        const isMain = ['front', 'sideL', 'sideR', 'back'].includes(name);
        const k = isMain ? 1 : 0.6;
        explode[name] = {
            pos: v((rand() - 0.5) * 0.5 * k, (rand() - 0.5) * 0.5 * k, (0.35 + rand() * 0.6) * k),
            rot: v((rand() - 0.5) * 0.5, (rand() - 0.5) * 0.6, (rand() - 0.5) * 0.25),
        };
    }

    function dispose() {
        root.removeFromParent();
        disposables.forEach((d) => d.dispose());
        textures.front?.dispose();
        textures.back?.dispose();
    }

    return { root, assembly, parts, explode, netWidth: 2 * (W + D), dispose };
}

/**
 * Estado animable de la caja. Anime.js interpola estos números (0 → 1 en
 * cada pliegue) y applyBoxState() los traduce a rotaciones de los pivotes.
 */
export function createBoxState() {
    return {
        explode: 1,   // 1 = piezas separadas y suspendidas, 0 = unidas
        sideL: 0, sideR: 0, back: 0,
        dustBotL: 0, dustBotR: 0, bottom: 0,
        dustTopL: 0, dustTopR: 0, lid: 0, tuck: 0,
        netShift: 1,  // centra la plantilla extendida; 0 = caja cerrada centrada
        tilt: -0.85,  // inclinación de la plantilla hacia atrás (rad)
        yaw: 0,       // giro de la caja (rad)
        lift: 0.75,   // altura extra sobre la posición final
        shadow: 0,    // opacidad de la sombra de contacto
    };
}

/** Ángulo final de cada pliegue (rad). Signo según el lado hacia el interior. */
const FOLD = {
    sideL: ['y', -HALF_PI], sideR: ['y', HALF_PI], back: ['y', HALF_PI],
    dustTopL: ['x', -HALF_PI], dustTopR: ['x', -HALF_PI], lid: ['x', -HALF_PI], tuck: ['x', -HALF_PI],
    dustBotL: ['x', HALF_PI], dustBotR: ['x', HALF_PI], bottom: ['x', HALF_PI],
};

export function applyBoxState(box, s, idle = { float: 0, sway: 0 }) {
    for (const [name, [axis, angle]] of Object.entries(FOLD)) {
        box.parts[name].pivot.rotation[axis] = angle * s[name];
    }
    for (const [name, { pos, rot }] of Object.entries(box.explode)) {
        const { slot } = box.parts[name];
        slot.position.set(pos.x * s.explode, pos.y * s.explode, pos.z * s.explode);
        slot.rotation.set(rot.x * s.explode, rot.y * s.explode, rot.z * s.explode);
    }
    // La plantilla extendida va de -W/2-D a W/2+D+W: se centra mientras está plana.
    box.assembly.position.x = -(BOX.width / 2) * s.netShift;
    box.root.rotation.set(s.tilt, s.yaw + idle.sway, 0);
    box.root.position.y = s.lift + idle.float;
}
