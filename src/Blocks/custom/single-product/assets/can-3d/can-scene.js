// Three.js stage for the 3D flavor picker — a vanilla port of the
// iphone-configurator's R3F <Canvas> (github.com/hyamero/iphone-configurator):
//
//   R3F / drei                          here
//   ----------------------------------  ------------------------------------
//   <PerspectiveCamera fov makeDefault> PerspectiveCamera
//   <Lights/> (ambient + spot + dir)    addLights()
//   <Environment preset="city">         RoomEnvironment via PMREM (no HDR fetch)
//   useGLTF('/iphone.gltf')             loadCanModel() — GLTFLoader + ./soda_can.glb
//   material-color={color.body}         label texture swap per flavor
//   <shadowMaterial> plane              same, ShadowMaterial
//   <OrbitControls> polar-locked        same, polar-locked, no zoom/pan
//   gsap body bg tween                  left to the caller (CSS transition)
//
// Framework-free on purpose — prototyped in _mockups/single-product-3d/ and
// lazy-loaded by ../index.js, so three.js only ships to pages that show a can.

import {
	AmbientLight,
	Box3,
	CanvasTexture,
	DirectionalLight,
	Group,
	MathUtils,
	Mesh,
	MeshBasicMaterial,
	NeutralToneMapping,
	PCFShadowMap,
	PMREMGenerator,
	PerspectiveCamera,
	PlaneGeometry,
	SRGBColorSpace,
	Scene,
	ShadowMaterial,
	TextureLoader,
	Timer,
	Vector3,
	WebGLRenderer,
} from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { drawLabel, loadLabelFonts } from './label.js';

// Realistic can model (CC-BY-4.0, "Soda Can" by sunich / Sketchfab — see
// CREDITS.md next to this file; the attribution must stay visible to users).
// webpack emits it as an asset URL (see webpack.config.js glb rule) and
// GLTFLoader fetches it lazily with the rest of this chunk.
import canModelUrl from './soda_can.glb';

// 12 oz can, in metres-ish scene units (66 mm Ø × 122 mm tall).
const CAN = {
	radius: 0.33,
	wallBottom: 0.1,
	wallTop: 1.05,
	height: 1.215,
};

const SPIN_MS = 900;

export function supportsWebGL() {
	try {
		const c = document.createElement('canvas');
		return Boolean(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
	} catch {
		return false;
	}
}

/**
 * @param {HTMLElement} container  Sized element the canvas fills.
 * @param {object}      opts
 * @param {object[]}    opts.flavors   Flavor objects (cardBg, nameColor, name, labelImage?…).
 * @param {number}      opts.activeId
 */
export async function createCanScene(container, { flavors, activeId }) {
	await loadLabelFonts();

	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

	const renderer = new WebGLRenderer({ antialias: true, alpha: true });
	renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
	renderer.outputColorSpace = SRGBColorSpace;
	// Neutral keeps the brand hexes on the label close to their sRGB values;
	// ACES/AgX would shift the package colors noticeably.
	renderer.toneMapping = NeutralToneMapping;
	renderer.shadowMap.enabled = true;
	renderer.shadowMap.type = PCFShadowMap;
	renderer.setClearColor(0x000000, 0);

	const canvas = renderer.domElement;
	canvas.className = 'yb-single-product__canCanvas';
	canvas.setAttribute('role', 'img');
	container.appendChild(canvas);

	const scene = new Scene();
	const pmrem = new PMREMGenerator(renderer);
	const envTexture = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
	scene.environment = envTexture;
	scene.environmentIntensity = 0.85;
	pmrem.dispose();

	const camera = new PerspectiveCamera(30, 1, 0.1, 50);

	addLights(scene);

	const { group: can, label } = await loadCanModel();
	can.position.y = -CAN.height / 2;
	scene.add(can);

	const floor = new Mesh(
		new PlaneGeometry(6, 6),
		new ShadowMaterial({ opacity: 0.12 })
	);
	floor.rotation.x = -Math.PI / 2;
	floor.position.y = -CAN.height / 2 - 0.001;
	floor.receiveShadow = true;
	scene.add(floor);

	// Contact shadow — a soft radial blob that grounds the can regardless of
	// the key light angle (drei's <ContactShadows> without the extra pass).
	const blob = new Mesh(
		new PlaneGeometry(1, 1),
		new MeshBasicMaterial({ map: radialTexture(), transparent: true, depthWrite: false, toneMapped: false })
	);
	blob.rotation.x = -Math.PI / 2;
	blob.position.y = -CAN.height / 2;
	scene.add(blob);

	const controls = new OrbitControls(camera, canvas);
	const polar = Math.PI / 2.12;
	controls.minPolarAngle = polar;
	controls.maxPolarAngle = polar;
	controls.enableZoom = false;
	controls.enablePan = false;
	controls.enableDamping = true;
	controls.dampingFactor = 0.08;
	controls.rotateSpeed = 0.7;
	controls.target.set(0, 0, 0);
	// OrbitControls sets touch-action:none, which traps page scroll on phones
	// whenever a finger lands on the can. pan-y hands vertical swipes back to
	// the browser and keeps horizontal drags for spinning.
	canvas.style.touchAction = 'pan-y';

	// --- Label textures -----------------------------------------------------

	const textures = new Map();
	const maxAniso = renderer.capabilities.getMaxAnisotropy();

	function textureFor(flavor) {
		if (textures.has(flavor.id)) {
			return textures.get(flavor.id);
		}
		let tex;
		if (flavor.labelImage) {
			tex = new TextureLoader().load(flavor.labelImage, () => requestRender());
		} else {
			tex = new CanvasTexture(drawLabel(flavor));
		}
		tex.colorSpace = SRGBColorSpace;
		tex.anisotropy = maxAniso;
		textures.set(flavor.id, tex);
		return tex;
	}

	const byId = new Map(flavors.map((f) => [f.id, f]));
	let current = byId.get(activeId) || flavors[0];
	label.material.map = textureFor(current);
	label.material.needsUpdate = true;
	setAria(current);

	// Warm the rest so the first swap doesn't hitch on a canvas draw + upload.
	const idle = window.requestIdleCallback || ((cb) => setTimeout(cb, 200));
	idle(() => flavors.forEach((f) => renderer.initTexture(textureFor(f))));

	function setAria(flavor) {
		canvas.setAttribute('aria-label', `3D view of the ${flavor.name} can. Drag to rotate.`);
	}

	// --- Flavor swap --------------------------------------------------------

	let spin = null;

	/**
	 * Spin to a flavor. Resolves once the can has landed, so the caller can
	 * hold a page navigation until the animation is done.
	 */
	function setFlavor(id) {
		const next = byId.get(id);
		if (!next || next === current) {
			return Promise.resolve();
		}
		current = next;
		setAria(next);

		if (reducedMotion.matches) {
			label.material.map = textureFor(next);
			requestRender();
			return Promise.resolve();
		}
		// A swap mid-spin finishes the running turn first.
		spin?.done();
		let done;
		const landed = new Promise((resolve) => (done = resolve));
		// One full turn; the label swaps at the half-way point while the back
		// of the can faces the viewer, so the new front arrives facing out.
		spin = {
			start: performance.now(),
			from: can.rotation.y,
			swapped: false,
			texture: textureFor(next),
			done,
		};
		requestRender();
		return landed;
	}

	// --- Loop ---------------------------------------------------------------

	let raf = 0;
	let visible = true;
	const timer = new Timer();

	function frame() {
		raf = 0;
		timer.update();
		const t = timer.getElapsed();
		let animating = controls.update();

		if (spin) {
			const p = Math.min(1, (performance.now() - spin.start) / SPIN_MS);
			const e = easeInOutCubic(p);
			can.rotation.y = spin.from + e * Math.PI * 2;
			can.position.y = -CAN.height / 2 + Math.sin(Math.PI * p) * 0.08;
			if (!spin.swapped && p >= 0.5) {
				label.material.map = spin.texture;
				spin.swapped = true;
			}
			if (p >= 1) {
				can.rotation.y = spin.from % (Math.PI * 2);
				can.position.y = -CAN.height / 2;
				const { done } = spin;
				spin = null;
				done();
			}
			animating = true;
		} else if (!reducedMotion.matches) {
			// Idle bob, like a product on a turntable that's just been set down.
			can.position.y = -CAN.height / 2 + Math.sin(t * 1.4) * 0.012;
			animating = true;
		}

		renderer.render(scene, camera);
		if (animating && visible) {
			raf = requestAnimationFrame(frame);
		}
	}

	function requestRender() {
		if (!raf && visible) {
			raf = requestAnimationFrame(frame);
		}
	}

	controls.addEventListener('change', requestRender);

	const io = new IntersectionObserver(([entry]) => {
		visible = entry.isIntersecting && document.visibilityState === 'visible';
		if (visible) {
			requestRender();
		}
	});
	io.observe(container);

	const onVisibility = () => {
		visible = document.visibilityState === 'visible';
		requestRender();
	};
	document.addEventListener('visibilitychange', onVisibility);

	// Pull the camera back just far enough that the can — plus the spin hop
	// and its contact shadow — fits the box on both axes. The hero column is
	// a tall 300×500 slot on desktop and squarer on phones; one fixed
	// distance would crop one or leave the can tiny in the other.
	function fitCamera() {
		const tan = Math.tan(MathUtils.degToRad(camera.fov / 2));
		const halfH = CAN.height / 2 + 0.16;
		const halfW = 0.5;
		const dist = Math.max(halfH / tan, halfW / (tan * camera.aspect));
		const offset = camera.position.clone().sub(controls.target);
		if (offset.lengthSq() === 0) {
			offset.setFromSphericalCoords(1, polar, 0);
		}
		camera.position.copy(controls.target).add(offset.setLength(dist));
		controls.update();
	}

	const ro = new ResizeObserver(() => {
		const { clientWidth: w, clientHeight: h } = container;
		if (!w || !h) {
			return;
		}
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		camera.updateProjectionMatrix();
		fitCamera();
		requestRender();
	});
	ro.observe(container);

	requestRender();

	return {
		setFlavor,
		canvas,
		dispose() {
			cancelAnimationFrame(raf);
			ro.disconnect();
			io.disconnect();
			document.removeEventListener('visibilitychange', onVisibility);
			controls.dispose();
			textures.forEach((tex) => tex.dispose());
			envTexture.dispose();
			scene.traverse((o) => {
				if (o.isMesh) {
					o.geometry.dispose();
					o.material.map?.dispose();
					o.material.dispose();
				}
			});
			renderer.dispose();
			canvas.remove();
		},
	};
}

function addLights(scene) {
	scene.add(new AmbientLight(0xffffff, 0.35));

	const key = new DirectionalLight(0xffffff, 1.6);
	key.position.set(1.2, 6, 2.2);
	key.castShadow = true;
	key.shadow.mapSize.set(1024, 1024);
	key.shadow.camera.left = -1.5;
	key.shadow.camera.right = 1.5;
	key.shadow.camera.top = 1.5;
	key.shadow.camera.bottom = -1.5;
	key.shadow.radius = 6;
	key.shadow.bias = -0.0005;
	scene.add(key);

	// Rim from behind-left gives the aluminium shoulder its edge highlight.
	const rim = new DirectionalLight(0xffffff, 1.1);
	rim.position.set(-3, 2, -3);
	scene.add(rim);
}

// Loads the glTF can and fits it to the scene's coordinates: upright along Y,
// base on y = 0, centred on X/Z and exactly CAN.height tall, so the camera
// framing, floor and contact shadow built for the old procedural can still
// line up. Returns the group to spin plus the label mesh whose map swaps per
// flavor. The model's own label UVs are a full cylindrical wrap matching the
// 2.18:1 art from label.js — u = 0.5 faces the camera, no offset needed.
async function loadCanModel() {
	const gltf = await new GLTFLoader().loadAsync(canModelUrl);
	const model = gltf.scene;

	let label = null;
	model.traverse((o) => {
		if (!o.isMesh) {
			return;
		}
		o.castShadow = true;
		o.receiveShadow = true;
		if (o.material?.name === 'Label') {
			label = o;
			// The model ships the label as a metallic material, which renders a
			// flat print coppery; a printed label is non-metallic.
			o.material.metalness = 0;
			o.material.roughness = 0.5;
			o.material.map?.dispose(); // drop the baked placeholder wrap
			o.material.map = null;
		}
	});

	// Parent first, then measure/transform through the group so updateMatrixWorld
	// cascades to the nested meshes (measuring the standalone scene doesn't).
	const group = new Group();
	group.add(model);
	// GLTFLoader already bakes Sketchfab's Z-up→Y-up root transform, so the can
	// arrives standing up along Y — no extra rotation needed.
	group.updateMatrixWorld(true);

	// Scale to CAN.height, then sit the base on y = 0 and centre on X/Z.
	const size = new Box3().setFromObject(group).getSize(new Vector3());
	model.scale.multiplyScalar(CAN.height / size.y);
	group.updateMatrixWorld(true);

	const fitted = new Box3().setFromObject(group);
	const center = fitted.getCenter(new Vector3());
	model.position.x -= center.x;
	model.position.z -= center.z;
	model.position.y -= fitted.min.y;
	group.updateMatrixWorld(true);

	return { group, label };
}

function radialTexture() {
	const c = document.createElement('canvas');
	c.width = c.height = 128;
	const ctx = c.getContext('2d');
	const g = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
	g.addColorStop(0, 'rgba(0,0,0,0.38)');
	g.addColorStop(0.45, 'rgba(0,0,0,0.16)');
	g.addColorStop(1, 'rgba(0,0,0,0)');
	ctx.fillStyle = g;
	ctx.fillRect(0, 0, 128, 128);
	return new CanvasTexture(c);
}

function easeInOutCubic(x) {
	return x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2;
}
