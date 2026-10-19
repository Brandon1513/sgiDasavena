import * as THREE from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { BOX } from './config.js';

const FOV = 30;

/**
 * Píxeles por punto. Se renderiza al menos a 1.5× aunque la pantalla sea
 * 1× (supermuestreo: bordes y texto del empaque más finos) y hasta 2× en
 * pantallas de alta densidad. Como el lienzo ocupa toda la pantalla, el
 * total se limita a ≈ 7 MP para no castigar equipos de oficina.
 */
const MAX_PIXELS = 7e6;
function pixelRatioFor(width, height) {
    const dpr = window.devicePixelRatio || 1;
    let ratio = Math.min(Math.max(dpr, 1.5), 2);
    const pixels = width * height * ratio * ratio;
    if (pixels > MAX_PIXELS) ratio = Math.max(1, Math.sqrt(MAX_PIXELS / (width * height)));
    return ratio;
}

/** Sombra de contacto suave (degradado radial) para asentar la caja en el piso. */
function createContactShadowTexture() {
    const size = 256;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');
    const g = ctx.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
    g.addColorStop(0, 'rgba(58,34,20,0.55)');
    g.addColorStop(0.45, 'rgba(58,34,20,0.22)');
    g.addColorStop(1, 'rgba(58,34,20,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, size, size);
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    return tex;
}

/**
 * Crea renderer, escena, cámara, luces y piso dentro de `container`.
 * Devuelve las piezas necesarias para el resto de módulos y un dispose().
 */
export function createScene(container) {
    const width = container.clientWidth || 1;
    const height = container.clientHeight || 1;

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'default' });
    renderer.setPixelRatio(pixelRatioFor(width, height));
    renderer.setSize(width, height, false);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    // Neutral conserva los colores del arte (morado y kraft) mejor que ACES.
    renderer.toneMapping = THREE.NeutralToneMapping;
    renderer.toneMappingExposure = 1.0;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.setClearColor(0x000000, 0); // el fondo lo pinta CSS
    renderer.domElement.classList.add('caja3d-canvas');
    renderer.domElement.setAttribute('aria-hidden', 'true');
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();

    // Entorno de estudio muy tenue: da volumen sin reflejos que tapen el texto.
    const pmrem = new THREE.PMREMGenerator(renderer);
    const roomEnv = new RoomEnvironment();
    const envTexture = pmrem.fromScene(roomEnv, 0.04).texture;
    scene.environment = envTexture;
    scene.environmentIntensity = 0.45;
    roomEnv.dispose?.();
    pmrem.dispose();

    const camera = new THREE.PerspectiveCamera(FOV, width / height, 0.1, 60);

    // ── Iluminación de estudio ──
    const hemi = new THREE.HemisphereLight(0xfff6ea, 0xb39472, 0.75);
    scene.add(hemi);

    const key = new THREE.DirectionalLight(0xfff1df, 2.1);
    key.position.set(3.2, 6.5, 5.2);
    key.castShadow = true;
    const shadowSize = width < 640 ? 1024 : 2048;
    key.shadow.mapSize.set(shadowSize, shadowSize);
    key.shadow.camera.left = -4.5;
    key.shadow.camera.right = 4.5;
    key.shadow.camera.top = 4.5;
    key.shadow.camera.bottom = -4.5;
    key.shadow.camera.near = 1;
    key.shadow.camera.far = 20;
    key.shadow.radius = 6;
    key.shadow.bias = -0.0006;
    key.shadow.normalBias = 0.02;
    scene.add(key);

    const fill = new THREE.DirectionalLight(0xf0e6ff, 0.55); // relleno frío con un toque morado
    fill.position.set(-5, 2.2, 3);
    scene.add(fill);

    const rim = new THREE.DirectionalLight(0xffffff, 0.8); // contraluz para separar del fondo
    rim.position.set(-2.5, 4, -5.5);
    scene.add(rim);

    // ── Piso: solo recibe sombra, el color lo da el fondo CSS ──
    const floorY = -BOX.height / 2 - BOX.hoverHeight;
    const floorGeo = new THREE.PlaneGeometry(30, 30);
    const floorMat = new THREE.ShadowMaterial({ color: 0x3a2214, opacity: 0.16 });
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = floorY;
    floor.receiveShadow = true;
    scene.add(floor);

    const contactTex = createContactShadowTexture();
    const contactGeo = new THREE.PlaneGeometry(1, 1);
    const contactMat = new THREE.MeshBasicMaterial({ map: contactTex, transparent: true, depthWrite: false, opacity: 0 });
    const contact = new THREE.Mesh(contactGeo, contactMat);
    contact.rotation.x = -Math.PI / 2;
    contact.position.y = floorY + 0.002;
    contact.scale.set(BOX.width * 1.9, BOX.depth * 3.2, 1);
    contact.renderOrder = -1;
    const contactPivot = new THREE.Group(); // gira con la caja para que la sombra la siga
    contactPivot.add(contact);
    scene.add(contactPivot);

    function setSize(w, h) {
        if (!w || !h) return;
        renderer.setPixelRatio(pixelRatioFor(w, h));
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }

    function dispose() {
        floorGeo.dispose();
        floorMat.dispose();
        contactGeo.dispose();
        contactMat.dispose();
        contactTex.dispose();
        envTexture.dispose();
        renderer.dispose();
        renderer.forceContextLoss?.();
        renderer.domElement.remove();
    }

    return { renderer, scene, camera, floorY, contact, contactPivot, setSize, dispose };
}

/**
 * Coloca la cámara en órbita alrededor del producto y calcula la distancia
 * necesaria para que `fitWidth × fitHeight` quepa en pantalla con cualquier
 * proporción (escritorio horizontal o móvil vertical).
 *
 * `region` es la fracción de la pantalla reservada al producto: { x: 0.58 }
 * lo centra en el 58 % derecho (el texto va a la izquierda) y { y: 0.58 }
 * en el 58 % superior (el texto va abajo, en móvil). Se logra desplazando
 * la imagen con setViewOffset, sin mover la cámara ni la perspectiva.
 */
export function frameCamera(camera, { azimuth, elevation, fitWidth, fitHeight, zoom = 1, targetY = 0, region = { x: 1, y: 1 }, viewport }) {
    const vFov = THREE.MathUtils.degToRad(camera.fov);
    const tanHalf = Math.tan(vFov / 2);
    const distH = fitHeight / (2 * tanHalf * region.y);
    const distW = fitWidth / (2 * tanHalf * camera.aspect * region.x);
    const dist = Math.max(distH, distW) * zoom;

    if (viewport?.width && viewport?.height) {
        const { width: w, height: h } = viewport;
        camera.setViewOffset(w, h, -((1 - region.x) / 2) * w, ((1 - region.y) / 2) * h, w, h);
    }

    const cosE = Math.cos(elevation);
    camera.position.set(
        Math.sin(azimuth) * cosE * dist,
        targetY + Math.sin(elevation) * dist,
        Math.cos(azimuth) * cosE * dist,
    );
    camera.lookAt(0, targetY, 0);
}
