// Three.js stage for the 3D flavor picker — a vanilla port of the
// iphone-configurator's R3F <Canvas> (github.com/hyamero/iphone-configurator):
//
//   R3F / drei                          here
//   ----------------------------------  ------------------------------------
//   <PerspectiveCamera fov makeDefault> PerspectiveCamera
//   <Lights/> (ambient + spot + dir)    addLights()
//   <Environment preset="city">         RoomEnvironment via PMREM (no HDR fetch)
//   useGLTF('/iphone.gltf')             buildCan() — lathe body + label band
//   material-color={color.body}         label texture swap per flavor
//   <shadowMaterial> plane              same, ShadowMaterial
//   <OrbitControls> polar-locked        same, polar-locked, no zoom/pan
//   gsap body bg tween                  left to the caller (CSS transition)
//
// Framework-free on purpose — prototyped in _mockups/single-product-3d/ and
// lazy-loaded by ../index.js, so three.js only ships to pages that show a can.

import {
	AmbientLight,
	CanvasTexture,
	CapsuleGeometry,
	CylinderGeometry,
	DirectionalLight,
	Group,
	LatheGeometry,
	MathUtils,
	Mesh,
	MeshBasicMaterial,
	MeshPhysicalMaterial,
	MeshStandardMaterial,
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
	Vector2,
	WebGLRenderer,
} from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { drawLabel, loadLabelFonts } from './label.js';

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

	const { group: can, label } = buildCan();
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

function buildCan() {
	const group = new Group();

	// Half-profile (x = radius, y = height) revolved around Y.
	const profile = [
		[0, 0.055], [0.18, 0.04], [0.255, 0.004], [0.275, 0],
		[0.3, 0.012], [0.322, 0.05], [CAN.radius, CAN.wallBottom],
		[CAN.radius, CAN.wallTop], [0.322, 1.1], [0.3, 1.14],
		[0.278, 1.17], [0.272, 1.19], [0.277, 1.205], [0.272, CAN.height],
		[0.262, 1.205], [0.258, 1.185], [0, 1.185],
	].map(([x, y]) => new Vector2(x, y));

	// Brushed rather than mirror: at low roughness the RoomEnvironment's light
	// boxes show up as blotches on the shoulder.
	const metal = new MeshStandardMaterial({
		color: 0xd5d9dc,
		metalness: 1,
		roughness: 0.42,
	});
	const body = new Mesh(new LatheGeometry(profile, 96), metal);
	body.castShadow = true;
	body.receiveShadow = true;
	group.add(body);

	// Label band over the straight wall. thetaStart = π puts u = 0.5 — the
	// front of the artwork — on +Z, facing the camera.
	const labelHeight = CAN.wallTop - CAN.wallBottom;
	const label = new Mesh(
		new CylinderGeometry(CAN.radius + 0.0012, CAN.radius + 0.0012, labelHeight, 128, 1, true, Math.PI),
		new MeshPhysicalMaterial({
			roughness: 0.42,
			metalness: 0.05,
			clearcoat: 0.6,
			clearcoatRoughness: 0.22,
		})
	);
	label.position.y = CAN.wallBottom + labelHeight / 2;
	label.castShadow = true;
	group.add(label);

	// Pull tab.
	const tab = new Mesh(
		new CapsuleGeometry(0.035, 0.11, 4, 12),
		new MeshStandardMaterial({ color: 0xc4c8cc, metalness: 1, roughness: 0.35 })
	);
	tab.rotation.z = Math.PI / 2;
	tab.rotation.y = Math.PI / 2;
	tab.scale.set(1, 1, 0.18);
	tab.position.set(0, 1.19, 0.06);
	group.add(tab);

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
