import * as THREE from 'three';
import { BOX } from './config.js';

/**
 * Ingredientes del cereal que aparecen alrededor de la caja en la fase 4:
 * almendra entera y fileteada, pepita de calabaza, quinoa inflada, hojuela
 * integral, avena y chía (los mismos que ilustra el empaque).
 *
 * Cada uno tiene forma propia (gota, óvalo extruido con bisel, lámina
 * ondulada con contorno irregular) y textura pintada en canvas con relieve
 * (bumpMap). Los tamaños son cercanos a los reales respecto a la caja.
 * Se ubican a los lados y detrás del producto para no tapar nunca el frente.
 */

function mulberry32(seed) {
    return () => {
        seed |= 0; seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/* ─────────────────────────────────────────────────────────────
 * Texturas pintadas
 * ───────────────────────────────────────────────────────────── */

function paint(size, draw, { repeat = false } = {}) {
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');
    draw(ctx, size);
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.anisotropy = 4;
    if (repeat) tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    return tex;
}

function speckle(ctx, size, rand, count, colors, [rMin, rMax]) {
    for (let i = 0; i < count; i++) {
        ctx.fillStyle = colors[Math.floor(rand() * colors.length)];
        ctx.beginPath();
        ctx.ellipse(rand() * size, rand() * size, rMin + rand() * (rMax - rMin), rMin + rand() * (rMax - rMin), rand() * Math.PI, 0, Math.PI * 2);
        ctx.fill();
    }
}

/** Piel de almendra: café rojizo con estrías longitudinales onduladas y poros. */
function almondSkinTexture(rand) {
    return paint(256, (ctx, s) => {
        const g = ctx.createLinearGradient(0, 0, s, 0);
        g.addColorStop(0, '#7a4322'); g.addColorStop(0.5, '#93552d'); g.addColorStop(1, '#7a4322');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, s, s);
        for (let i = 0; i < 70; i++) {
            const x0 = rand() * s;
            ctx.strokeStyle = rand() > 0.5 ? `rgba(60,28,12,${0.25 + rand() * 0.3})` : `rgba(190,120,70,${0.2 + rand() * 0.25})`;
            ctx.lineWidth = 0.8 + rand() * 2.2;
            ctx.beginPath();
            for (let y = 0; y <= s; y += 8) {
                const x = x0 + Math.sin(y * 0.05 + i) * 3 + (rand() - 0.5) * 1.5;
                y === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.stroke();
        }
        speckle(ctx, s, rand, 260, ['rgba(55,25,10,.45)', 'rgba(200,135,80,.35)'], [0.6, 1.6]);
    }, { repeat: true });
}

/** Corte de almendra: crema marfil con fibra radial fina. */
function almondCutTexture(rand) {
    return paint(256, (ctx, s) => {
        const g = ctx.createRadialGradient(s / 2, s / 2, 4, s / 2, s / 2, s * 0.55);
        g.addColorStop(0, '#f6e8cc'); g.addColorStop(0.7, '#ecd6ac'); g.addColorStop(1, '#d9b98a');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, s, s);
        for (let i = 0; i < 140; i++) {
            const a = rand() * Math.PI * 2;
            const r0 = rand() * s * 0.1, r1 = s * (0.2 + rand() * 0.35);
            ctx.strokeStyle = `rgba(190,150,95,${0.08 + rand() * 0.12})`;
            ctx.lineWidth = 0.6 + rand();
            ctx.beginPath();
            ctx.moveTo(s / 2 + Math.cos(a) * r0, s / 2 + Math.sin(a) * r0);
            ctx.lineTo(s / 2 + Math.cos(a) * r1, s / 2 + Math.sin(a) * r1);
            ctx.stroke();
        }
        speckle(ctx, s, rand, 120, ['rgba(170,125,70,.25)', 'rgba(255,250,235,.4)'], [0.5, 1.4]);
    }, { repeat: true });
}

/** Pepita de calabaza: verde profundo al centro, borde verde olivo claro. */
function pepitaTexture(rand) {
    return paint(256, (ctx, s) => {
        const g = ctx.createRadialGradient(s / 2, s / 2, 2, s / 2, s / 2, s * 0.5);
        g.addColorStop(0, '#3b5a1f'); g.addColorStop(0.55, '#4d6d27'); g.addColorStop(0.85, '#7d9a45'); g.addColorStop(1, '#a3b56a');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, s, s);
        for (let i = 0; i < 45; i++) {
            ctx.strokeStyle = `rgba(30,50,15,${0.12 + rand() * 0.15})`;
            ctx.lineWidth = 0.7 + rand();
            const x = s * (0.3 + rand() * 0.4);
            ctx.beginPath();
            ctx.moveTo(x, s * 0.08);
            ctx.quadraticCurveTo(x + (rand() - 0.5) * 30, s / 2, x, s * 0.92);
            ctx.stroke();
        }
        speckle(ctx, s, rand, 160, ['rgba(20,40,10,.3)', 'rgba(170,190,110,.3)'], [0.5, 1.5]);
    }, { repeat: true });
}

/** Contorno irregular de hojuela (radio con ruido). */
function flakeOutline(rand, points = 22) {
    const pts = [];
    const phase = rand() * 10;
    for (let i = 0; i < points; i++) {
        const a = (i / points) * Math.PI * 2;
        const r = 0.5 * (0.78 + Math.sin(a * 3 + phase) * 0.07 + rand() * 0.16);
        pts.push([Math.cos(a) * r * 1.08, Math.sin(a) * r * 0.92]);
    }
    return pts;
}

/**
 * Hojuela integral tostada: base dorada, bordes más tostados, granos de
 * quinoa y avena incrustados y motas de tostado. El canal alfa recorta el
 * contorno irregular sobre una lámina plana subdividida.
 */
function flakeTexture(rand, outline) {
    return paint(512, (ctx, s) => {
        const k = s / 256; // tamaños pensados en 256 px
        // Contorno suavizado (curvas por los puntos medios) para un borde sin dientes
        const pts = outline.map(([x, y]) => [(x + 0.5) * s, (y + 0.5) * s]);
        const mid = (a, b) => [(a[0] + b[0]) / 2, (a[1] + b[1]) / 2];
        const path = new Path2D();
        const start = mid(pts[pts.length - 1], pts[0]);
        path.moveTo(start[0], start[1]);
        pts.forEach((p, i) => {
            const m = mid(p, pts[(i + 1) % pts.length]);
            path.quadraticCurveTo(p[0], p[1], m[0], m[1]);
        });
        path.closePath();
        ctx.save();
        ctx.clip(path);
        ctx.fillStyle = '#c8924a';
        ctx.fillRect(0, 0, s, s);
        const g = ctx.createRadialGradient(s / 2, s / 2, s * 0.12, s / 2, s / 2, s * 0.52);
        g.addColorStop(0, 'rgba(235,190,115,.55)'); g.addColorStop(0.7, 'rgba(0,0,0,0)'); g.addColorStop(1, 'rgba(110,55,20,.65)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, s, s);
        speckle(ctx, s, rand, 900, ['rgba(120,62,22,.45)', 'rgba(90,45,15,.35)', 'rgba(240,205,140,.4)'], [0.6 * k, 2 * k]);
        // Granos incrustados: quinoa (redondos con anillo y brillo) y trocitos de avena
        for (let i = 0; i < 30; i++) {
            const x = s * (0.18 + rand() * 0.64), y = s * (0.18 + rand() * 0.64);
            if (rand() > 0.4) {
                const r = (3 + rand() * 2.5) * k;
                ctx.fillStyle = '#efdcae';
                ctx.beginPath(); ctx.arc(x, y, r, 0, Math.PI * 2); ctx.fill();
                ctx.strokeStyle = 'rgba(170,120,60,.6)'; ctx.lineWidth = k;
                ctx.beginPath(); ctx.arc(x, y, r * 0.75, 0, Math.PI * 2); ctx.stroke();
                ctx.fillStyle = 'rgba(255,250,235,.8)';
                ctx.beginPath(); ctx.arc(x - r * 0.35, y - r * 0.35, r * 0.25, 0, Math.PI * 2); ctx.fill();
            } else {
                ctx.fillStyle = 'rgba(228,196,135,.85)';
                ctx.beginPath(); ctx.ellipse(x, y, (6 + rand() * 4) * k, (3 + rand() * 2) * k, rand() * Math.PI, 0, Math.PI * 2); ctx.fill();
            }
        }
        // Borde tostado y ligeramente quemado
        ctx.strokeStyle = 'rgba(95,45,15,.55)';
        ctx.lineWidth = 5 * k;
        ctx.stroke(path);
        ctx.restore();
    });
}

/** Avena entera laminada: beige claro con estrías paralelas del rodillo. */
function oatTexture(rand) {
    return paint(256, (ctx, s) => {
        ctx.fillStyle = 'rgba(0,0,0,0)';
        ctx.clearRect(0, 0, s, s);
        ctx.save();
        ctx.beginPath();
        ctx.ellipse(s / 2, s / 2, s * 0.3, s * 0.47, 0, 0, Math.PI * 2);
        ctx.clip();
        ctx.fillStyle = '#e4cf9c';
        ctx.fillRect(0, 0, s, s);
        for (let x = s * 0.18; x < s * 0.82; x += 5 + rand() * 4) {
            ctx.strokeStyle = `rgba(160,125,70,${0.18 + rand() * 0.2})`;
            ctx.lineWidth = 1 + rand();
            ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x + (rand() - 0.5) * 6, s); ctx.stroke();
        }
        const g = ctx.createRadialGradient(s / 2, s / 2, s * 0.1, s / 2, s / 2, s * 0.48);
        g.addColorStop(0, 'rgba(255,245,215,.35)'); g.addColorStop(1, 'rgba(150,110,60,.45)');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, s, s);
        ctx.restore();
    });
}

/* ─────────────────────────────────────────────────────────────
 * Geometrías (unidad: largo 1 sobre el eje Y)
 * ───────────────────────────────────────────────────────────── */

/** Contorno de gota/óvalo: `point` afina el extremo superior. */
function seedShape(width, height, point, segments = 40) {
    const shape = new THREE.Shape();
    for (let i = 0; i <= segments; i++) {
        const t = (i / segments) * Math.PI * 2;
        const taper = 1 - point * (1 + Math.cos(t)) / 2;
        const x = (width / 2) * Math.sin(t) * taper;
        const y = (height / 2) * Math.cos(t);
        i === 0 ? shape.moveTo(x, y) : shape.lineTo(x, y);
    }
    return shape;
}

/** Sólido extruido con bisel, centrado; caras = grupo 0, canto = grupo 1. */
function extrudedSeed(shape, depth, bevel) {
    const geo = new THREE.ExtrudeGeometry(shape, {
        depth, bevelEnabled: true, bevelThickness: bevel, bevelSize: bevel * 0.8, bevelSegments: 3, curveSegments: 24,
    });
    geo.center();
    return geo;
}

/** Almendra entera: elipsoide con punta, ligeramente curvada y aplanada. */
function almondGeometry() {
    const geo = new THREE.SphereGeometry(0.5, 36, 24);
    const p = geo.attributes.position;
    for (let i = 0; i < p.count; i++) {
        let x = p.getX(i), y = p.getY(i), z = p.getZ(i);
        const v = y / 0.5; // -1 (base) … 1 (punta)
        const taper = v > 0 ? 1 - 0.62 * Math.pow(v, 1.1) : 1 - 0.08 * v * v;
        x *= 0.62 * taper;
        z *= 0.4 * taper;
        x += 0.04 * v * v; // leve curvatura
        p.setXYZ(i, x, y, z);
    }
    geo.computeVertexNormals();
    return geo;
}

/** Lámina subdividida con ondulación (hojuelas y avena). */
function wavyPlane(rand, amount) {
    const geo = new THREE.PlaneGeometry(1, 1, 18, 18);
    const p = geo.attributes.position;
    const a = rand() * 6, b = rand() * 6;
    for (let i = 0; i < p.count; i++) {
        const x = p.getX(i), y = p.getY(i);
        const z = amount * (0.9 * (x * x + y * y) - 0.25) + amount * 0.35 * Math.sin(x * 7 + a) * Math.cos(y * 6 + b);
        p.setZ(i, z);
    }
    geo.computeVertexNormals();
    return geo;
}

/* ─────────────────────────────────────────────────────────────
 * Ingredientes
 * ───────────────────────────────────────────────────────────── */

export function createIngredients(floorY) {
    const rand = mulberry32(2010);
    const disposables = [];
    const track = (o) => { disposables.push(o); return o; };

    // Texturas
    const skinTex = track(almondSkinTexture(rand));
    const cutTex = track(almondCutTexture(rand));
    const pepitaTex = track(pepitaTexture(rand));
    const oatTex = track(oatTexture(rand));
    const flakeOutlines = [0, 1, 2].map(() => flakeOutline(rand));
    const flakeTexs = flakeOutlines.map((o) => track(flakeTexture(rand, o)));
    // Las caras extruidas usan como UV las coordenadas del contorno (centrado
    // en 0): se desplaza medio ciclo para que el centro de la textura coincida.
    cutTex.offset.set(0.5, 0.5);
    pepitaTex.offset.set(0.5, 0.5);

    // Materiales
    const std = (o) => track(new THREE.MeshStandardMaterial({ metalness: 0, ...o }));
    const skinMat = std({ map: skinTex, bumpMap: skinTex, bumpScale: 2.2, roughness: 0.62 });
    const cutMat = std({ map: cutTex, bumpMap: cutTex, bumpScale: 1, roughness: 0.78 });
    const pepitaMat = std({ map: pepitaTex, bumpMap: pepitaTex, bumpScale: 1.2, roughness: 0.55 });
    const pepitaRimMat = std({ color: '#8fa557', roughness: 0.65 });
    const quinoaMat = std({ color: '#f1e4c2', roughness: 0.82 });
    const quinoaRingMat = std({ color: '#c4a062', roughness: 0.75 });
    const oatMat = std({ map: oatTex, bumpMap: oatTex, bumpScale: 1.4, roughness: 0.85, alphaTest: 0.5, side: THREE.DoubleSide });
    const flakeMats = flakeTexs.map((t) => std({ map: t, bumpMap: t, bumpScale: 2.4, roughness: 0.82, alphaTest: 0.5, side: THREE.DoubleSide }));
    const chiaMats = [std({ color: '#3a3430', roughness: 0.5 }), std({ color: '#6f665d', roughness: 0.5 })];

    // Geometrías (compartidas)
    const almondGeo = track(almondGeometry());
    const slicedGeo = track(extrudedSeed(seedShape(0.62, 1, 0.35), 0.05, 0.02));
    const pepitaGeo = track(extrudedSeed(seedShape(0.58, 1, 0.55), 0.04, 0.035));
    const quinoaGeo = track(new THREE.SphereGeometry(0.5, 16, 12));
    const quinoaRingGeo = track(new THREE.TorusGeometry(0.45, 0.11, 10, 32));
    const flakeGeos = [0, 1, 2].map(() => track(wavyPlane(rand, 0.09)));
    const oatGeo = track(wavyPlane(rand, 0.05));
    const chiaGeo = track(new THREE.SphereGeometry(0.5, 10, 8));

    const KINDS = {
        almond: { size: 0.31, build: () => new THREE.Mesh(almondGeo, skinMat) },
        sliced: { size: 0.3, build: () => new THREE.Mesh(slicedGeo, [cutMat, skinMat]) },
        pepita: { size: 0.2, build: () => new THREE.Mesh(pepitaGeo, [pepitaMat, pepitaRimMat]) },
        quinoa: {
            size: 0.06,
            build: () => {
                const g = new THREE.Group();
                const seed = new THREE.Mesh(quinoaGeo, quinoaMat);
                seed.scale.set(1, 0.78, 1);
                const ring = new THREE.Mesh(quinoaRingGeo, quinoaRingMat);
                ring.rotation.x = Math.PI / 2;
                g.add(seed, ring);
                return g;
            },
        },
        flake: {
            size: 0.37,
            build: () => {
                const k = Math.floor(rand() * 3);
                return new THREE.Mesh(flakeGeos[k], flakeMats[k]);
            },
        },
        oat: { size: 0.18, build: () => new THREE.Mesh(oatGeo, oatMat) },
        chia: {
            size: 0.032,
            build: () => {
                const m = new THREE.Mesh(chiaGeo, chiaMats[rand() > 0.5 ? 1 : 0]);
                m.scale.set(0.62, 1, 0.42);
                return m;
            },
        },
    };

    const group = new THREE.Group();
    const items = [];
    const sideMin = BOX.width / 2 + 0.45; // fuera de la silueta frontal de la caja

    function add(kind, position, floating) {
        const def = KINDS[kind];
        const object = def.build();
        object.traverse((o) => { if (o.isMesh) o.castShadow = def.size > 0.1; });
        const base = def.size * (0.85 + rand() * 0.35);
        object.rotation.set(rand() * Math.PI, rand() * Math.PI, rand() * Math.PI);
        // El contenedor lleva posición y escala de aparición; el objeto conserva
        // su forma (escalas propias de quinoa y chía) y su rotación.
        const holder = new THREE.Group();
        holder.add(object);
        holder.position.copy(position);
        holder.scale.setScalar(0.0001);
        group.add(holder);
        items.push({
            holder,
            object,
            baseScale: base,
            base: position.clone(),
            spin: new THREE.Vector3((rand() - 0.5) * 0.5, (rand() - 0.5) * 0.6, (rand() - 0.5) * 0.4),
            rot0: object.rotation.clone(),
            phase: rand() * Math.PI * 2,
            delay: rand() * 0.55, // aparición escalonada dentro de la fase
            floating,
        });
    }

    // Suspendidos a los lados del producto: más piezas grandes y algunas semillas pequeñas
    const sideKinds = ['almond', 'sliced', 'pepita', 'flake', 'quinoa', 'oat', 'sliced', 'pepita', 'quinoa', 'flake', 'almond', 'oat'];
    for (let i = 0; i < 22; i++) {
        const side = i % 2 === 0 ? -1 : 1;
        add(sideKinds[i % sideKinds.length], new THREE.Vector3(
            side * (sideMin + rand() * 1.05),
            -1.2 + rand() * 2.9,
            -1.1 + rand() * 1.7,
        ), true);
    }
    // Racimos de quinoa y chía cerca de las piezas grandes
    for (let i = 0; i < 18; i++) {
        const side = i % 2 === 0 ? -1 : 1;
        add(i % 3 === 0 ? 'chia' : 'quinoa', new THREE.Vector3(
            side * (sideMin + 0.1 + rand() * 0.9),
            -1.0 + rand() * 2.6,
            -0.6 + rand() * 1.4,
        ), true);
    }
    // Algunos detrás, por encima de la tapa
    for (let i = 0; i < 6; i++) {
        add(['flake', 'almond', 'oat'][i % 3], new THREE.Vector3((rand() - 0.5) * 1.8, BOX.height / 2 + 0.35 + rand() * 0.5, -0.9 - rand() * 0.8), true);
    }
    // Unos cuantos sobre el piso, a los costados de la base
    for (let i = 0; i < 10; i++) {
        const side = i % 2 === 0 ? -1 : 1;
        add(['sliced', 'quinoa', 'flake', 'pepita', 'oat'][i % 5], new THREE.Vector3(side * (0.95 + rand() * 1.2), floorY + 0.02, 0.2 + rand() * 0.9), false);
    }
    // Los del piso quedan acostados
    items.filter((it) => !it.floating).forEach((it) => {
        it.rot0.set(-Math.PI / 2 + (rand() - 0.5) * 0.3, 0, rand() * Math.PI * 2);
        it.object.rotation.copy(it.rot0);
    });

    /**
     * appear: 0..1 (lo anima Anime.js). time: segundos para la deriva suave.
     * motion = false congela la deriva (prefers-reduced-motion).
     */
    function update(appear, time, motion) {
        for (const it of items) {
            const p = THREE.MathUtils.clamp((appear - it.delay) / 0.45, 0, 1);
            const e = 1 - Math.pow(1 - p, 3);
            it.holder.visible = p > 0;
            if (!it.holder.visible) continue;
            it.holder.scale.setScalar(Math.max(e * it.baseScale, 0.0001));
            const fall = (1 - e) * (it.floating ? 0.35 : 0.6);
            const t = motion ? time : 0;
            const bob = it.floating ? Math.sin(t * 0.9 + it.phase) * 0.06 : 0;
            it.holder.position.set(it.base.x, it.base.y + fall + bob, it.base.z);
            if (it.floating) {
                it.object.rotation.set(it.rot0.x + it.spin.x * t, it.rot0.y + it.spin.y * t, it.rot0.z + it.spin.z * t);
            }
        }
    }

    function dispose() {
        group.removeFromParent();
        disposables.forEach((d) => d.dispose());
    }

    return { group, update, dispose };
}
