// Three.js stage for the 3D flavor picker — a vanilla port of the
// iphone-configurator's R3F <Canvas> (github.com/hyamero/iphone-configurator):
//
//   R3F / drei                          here
//   ----------------------------------  ------------------------------------
//   <PerspectiveCamera fov makeDefault> PerspectiveCamera
//   <Lights/> (ambient + spot + dir)    addLights()
//   <Environment preset="city">         RoomEnvironment via PMREM (no HDR fetch)
//   useGLTF('/iphone.gltf')             loadModel() — GLTFLoader + can/pouch .glb
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
	Timer,
	Vector3,
	WebGLRenderer,
} from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';
import { drawLabel, loadLabelFonts, LABEL_W, LABEL_H } from './label.js';

// Realistic product models (both CC-BY-4.0 / Sketchfab — see CREDITS.md next to
// this file; attribution must stay visible to users). webpack emits them as
// asset URLs (see webpack.config.js glb rule) and GLTFLoader fetches the one
// this page needs lazily with the rest of this chunk.
import canModelUrl from './soda_can.glb';
import pouchModelUrl from './cofe_pouch.glb';

// Per-model knobs, found via the mockup harness (.context/glb-mockup):
//  - labelMat: the material whose map we swap per flavor
//  - metalness: force non-metallic so a flat print doesn't read coppery
//    (null = leave the model's own value)
//  - flipY: texture orientation the model's label UVs expect
//  - backMat: a second face that takes the product's back art (pouch only)
//  - rect: the UV island the face samples, [u0, v0, u1, v1] with v top-down,
//    when it isn't the full 0–1 texture; art is "contained" centred in it
//  - aspect: canvas w/h that renders that island undistorted on the face
//  - panels: where each product tab's content sits on the packaging, as the
//    texture coordinate u (0–1 round the model, 0.5 = front) to turn to. On
//    the can the facts block and benefits icons are side panels of the wrap;
//    on the pouch the facts are the back face and benefits are on the front.
const MODELS = {
	can: {
		url: canModelUrl,
		labelMat: 'Label',
		backMat: null,
		metalness: 0,
		flipY: true,
		rect: null,
		aspect: null,
		panels: { description: 0.5, cannafacts: 0.82, benefits: 0.31 },
	},
	pouch: {
		url: pouchModelUrl,
		labelMat: 'green_f',
		backMat: 'green_b',
		metalness: null,
		flipY: false,
		// The faces' UV island is u 0.199–0.806, v 0.092–0.933 (the whole
		// printable panel). The art goes in a rectangle inset within it — 85%
		// of the width, centred — leaving body colour around it like the
		// printed pouch, rather than running edge to edge.
		rect: [0.245, 0.245, 0.76, 0.78],
		aspect: 0.831,
		panels: { description: 0.5, cannafacts: 0, benefits: 0.5 },
	},
};

// 12 oz can, in metres-ish scene units (66 mm Ø × 122 mm tall).
const CAN = {
	radius: 0.33,
	wallBottom: 0.1,
	wallTop: 1.05,
	height: 1.215,
};

const SPIN_MS = 900;

// How much bigger than its hero box the canvas renders (must match the
// .yb-single-product__canCanvas width/height in the block SCSS): 25% each
// side and 30% below give the floor/contact shadow room to fall.
const CANVAS_SCALE = { x: 1.5, y: 1.4 };

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
 * @param {string}      [opts.model]   Which model to load ('can' | 'pouch'); defaults to can.
 */
export async function createCanScene(container, { flavors, activeId, model }) {
	const spec = MODELS[model] || MODELS.can;
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
	scene.environmentIntensity = 0.9;
	pmrem.dispose();

	const camera = new PerspectiveCamera(30, 1, 0.1, 50);

	addLights(scene);

	const { group: can, label, back } = await loadModel(spec);
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

	// Settings every label texture needs.
	function prepare(tex) {
		tex.colorSpace = SRGBColorSpace;
		tex.anisotropy = maxAniso;
		// The can's and pouch's label UVs expect opposite vertical orientation.
		tex.flipY = spec.flipY;
		return tex;
	}

	// Composite label art onto a cardBg-filled canvas. The art ships with a
	// transparent background: the body colour shows through, exactly like the
	// printed product. With `spec.rect` the art is contained, centred, inside
	// that UV island (the pouch's faces sample only part of the texture) and
	// `spec.aspect` sizes the canvas so the island renders undistorted.
	// Without an image it's a plain cardBg plate — the placeholder while art
	// loads, and the permanent back of a pouch that has no back art.
	function composite(flavor, img) {
		const canvas = document.createElement('canvas');
		canvas.width = LABEL_W;
		if (spec.aspect) {
			canvas.height = Math.round(LABEL_W / spec.aspect);
		} else if (img) {
			canvas.height = Math.round((LABEL_W * img.naturalHeight) / img.naturalWidth);
		} else {
			canvas.height = LABEL_H;
		}
		const ctx = canvas.getContext('2d');
		ctx.fillStyle = flavor.cardBg || '#ffffff';
		ctx.fillRect(0, 0, canvas.width, canvas.height);
		if (img) {
			const [u0, v0, u1, v1] = spec.rect || [0, 0, 1, 1];
			const rx = u0 * canvas.width;
			const ry = v0 * canvas.height;
			const rw = (u1 - u0) * canvas.width;
			const rh = (v1 - v0) * canvas.height;
			const art = img.naturalWidth / img.naturalHeight;
			let dw = rw;
			let dh = rh;
			if (art > rw / rh) {
				dh = Math.round(rw / art);
			} else {
				dw = Math.round(rh * art);
			}
			ctx.drawImage(img, Math.round(rx + (rw - dw) / 2), Math.round(ry + (rh - dh) / 2), dw, dh);
		}
		return canvas;
	}

	// Texture for one face ('front' | 'back') of a flavor, cached per face.
	// Front falls back to the drawn label when there's no art; back has no
	// drawn fallback (it stays a plain body-colour plate).
	//
	// Art loads async: a plain cardBg placeholder shows until the image lands.
	// The final texture is then built fresh — a GPU texture can't be resized
	// in place (glCopySubTexture overflows) — and swapped in wherever the
	// placeholder was in use (the face, an in-flight spin, the cache).
	function textureFor(flavor, side = 'front') {
		const key = `${flavor.id}:${side}`;
		if (textures.has(key)) {
			return textures.get(key);
		}
		const mesh = side === 'back' ? back : label;
		const src = side === 'back' ? flavor.labelBackImage : flavor.labelImage;
		const spinKey = side === 'back' ? 'backTexture' : 'texture';
		let tex;
		if (src) {
			tex = new CanvasTexture(composite(flavor, null));
			const img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = () => {
				const placeholder = textures.get(key);
				const loaded = prepare(new CanvasTexture(composite(flavor, img)));
				textures.set(key, loaded);
				if (mesh && mesh.material.map === placeholder) {
					mesh.material.map = loaded;
					mesh.material.needsUpdate = true;
				}
				if (spin?.[spinKey] === placeholder) {
					spin[spinKey] = loaded;
				}
				placeholder.dispose();
				requestRender();
			};
			img.src = src;
		} else if (side === 'back') {
			tex = new CanvasTexture(composite(flavor, null));
		} else {
			tex = new CanvasTexture(drawLabel(flavor));
		}
		textures.set(key, prepare(tex));
		return tex;
	}

	// Put a flavor's art on every face the model has.
	function applyFlavorTextures(flavor) {
		label.material.map = textureFor(flavor);
		label.material.needsUpdate = true;
		if (back) {
			back.material.map = textureFor(flavor, 'back');
			back.material.needsUpdate = true;
		}
	}

	const byId = new Map(flavors.map((f) => [f.id, f]));
	let current = byId.get(activeId) || flavors[0];
	applyFlavorTextures(current);
	setAria(current);

	// Warm the rest so the first swap doesn't hitch on a canvas draw + upload.
	const idle = window.requestIdleCallback || ((cb) => setTimeout(cb, 200));
	// Only the fronts: a pouch back is built when a spin first needs it, and
	// warming every back too would double GPU memory for faces rarely seen.
	idle(() => flavors.forEach((f) => renderer.initTexture(textureFor(f))));

	function setAria(flavor) {
		canvas.setAttribute('aria-label', `3D view of the ${flavor.name} can. Drag to rotate.`);
	}

	// --- Flavor swap --------------------------------------------------------

	let spin = null;
	let turn = null;

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
			applyFlavorTextures(next);
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
			backTexture: back ? textureFor(next, 'back') : null,
			done,
		};
		requestRender();
		return landed;
	}

	// --- Turn to a label panel ----------------------------------------------

	// Turn the can so the label at texture coordinate `u` (0–1 around the
	// wrap, 0.5 = the front art) faces the camera. The tabs use it: nutrition
	// facts and the benefits icons live on the label's side panels. Goes the
	// short way round from wherever the can is, and respects the camera angle
	// the visitor may have dragged to.
	const TURN_MS = 650;
	function turnTo(u) {
		const target = controls.getAzimuthalAngle() + (0.5 - u) * Math.PI * 2;
		const from = can.rotation.y;
		const twoPi = Math.PI * 2;
		const delta = ((((target - from) % twoPi) + twoPi * 1.5) % twoPi) - Math.PI;
		if (reducedMotion.matches) {
			can.rotation.y = from + delta;
			requestRender();
			return;
		}
		turn = { start: performance.now(), from, to: from + delta };
		requestRender();
	}

	// Turn to the part of the packaging a product tab talks about (see
	// MODELS[].panels); unknown tabs face front.
	function turnToPanel(name) {
		turnTo(spec.panels?.[name] ?? 0.5);
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
				label.material.needsUpdate = true;
				if (back && spin.backTexture) {
					back.material.map = spin.backTexture;
					back.material.needsUpdate = true;
				}
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
		} else if (turn) {
			const p = Math.min(1, (performance.now() - turn.start) / TURN_MS);
			can.rotation.y = turn.from + (turn.to - turn.from) * easeInOutCubic(p);
			if (p >= 1) {
				turn = null;
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
	// The canvas renders larger than the hero box it sits in (CANVAS_SCALE, with
	// matching CSS) so the contact shadow has room instead of clipping at the
	// box edge. Fit the can to the BOX — the extra canvas area is margin — so
	// the can keeps the same on-screen size as before.
	function fitCamera() {
		const tan = Math.tan(MathUtils.degToRad(camera.fov / 2));
		const halfH = (CAN.height / 2 + 0.16) * CANVAS_SCALE.y;
		const halfW = 0.5 * CANVAS_SCALE.x;
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
		const cw = Math.round(w * CANVAS_SCALE.x);
		const ch = Math.round(h * CANVAS_SCALE.y);
		renderer.setSize(cw, ch, false);
		camera.aspect = cw / ch;
		camera.updateProjectionMatrix();
		fitCamera();
		requestRender();
	});
	ro.observe(container);

	requestRender();

	return {
		setFlavor,
		turnTo,
		turnToPanel,
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

// Lit so the whole label face reads — the fine print and side panels as much
// as the logo. A steep key lit the shoulder and left the lower half of the
// label in its own shade, so the key comes in lower and more frontal, a soft
// fill faces the label straight on, and ambient is lifted. The rim keeps the
// aluminium edges crisp.
function addLights(scene) {
	scene.add(new AmbientLight(0xffffff, 0.5));

	const key = new DirectionalLight(0xffffff, 1.3);
	key.position.set(1.5, 3.5, 4);
	key.castShadow = true;
	key.shadow.mapSize.set(1024, 1024);
	key.shadow.camera.left = -1.5;
	key.shadow.camera.right = 1.5;
	key.shadow.camera.top = 1.5;
	key.shadow.camera.bottom = -1.5;
	key.shadow.radius = 6;
	key.shadow.bias = -0.0005;
	scene.add(key);

	// Straight-on fill evens out the label face so text reads edge to edge.
	const fill = new DirectionalLight(0xffffff, 0.4);
	fill.position.set(0, 0.6, 6);
	scene.add(fill);

	// Rim from behind-left gives the aluminium shoulder its edge highlight.
	const rim = new DirectionalLight(0xffffff, 1.1);
	rim.position.set(-3, 2, -3);
	scene.add(rim);
}

// Loads a glTF product model (can or pouch) and fits it to the scene's
// coordinates: upright along Y, base on y = 0, centred on X/Z and exactly
// CAN.height tall, so the camera framing, floor and contact shadow built for
// the old procedural can still line up for either model. Returns the group to
// spin plus the label mesh whose map swaps per flavor (its UVs match the
// 2.18:1 art from label.js).
async function loadModel(spec) {
	// The models are pruned of the baked label art we overwrite, their other
	// maps shrunk to 512² WebP, and quantized + meshopt-compressed
	// (EXT_meshopt_compression; script in the workspace's .context/glb-opt).
	// The decoder is ~25 KB. Note the optimizer must keep TEXCOORD_0 even
	// though no texture references it once the label map is dropped — the
	// scene samples it at runtime.
	const loader = new GLTFLoader();
	loader.setMeshoptDecoder(MeshoptDecoder);
	const gltf = await loader.loadAsync(spec.url);
	const model = gltf.scene;

	let label = null;
	let back = null;
	model.traverse((o) => {
		if (!o.isMesh) {
			return;
		}
		o.castShadow = true;
		o.receiveShadow = true;
		const isLabel = o.material?.name === spec.labelMat;
		const isBack = spec.backMat && o.material?.name === spec.backMat;
		if (isLabel || isBack) {
			if (isLabel) {
				label = o;
			} else {
				back = o;
			}
			// Some models ship the label material metallic, which renders a flat
			// print coppery; force it non-metallic when the spec asks.
			if (spec.metalness !== null) {
				o.material.metalness = spec.metalness;
				o.material.roughness = 0.5;
			}
			o.material.map?.dispose(); // drop the baked placeholder art
			o.material.map = null;
		}
	});

	// Parent first, then measure/transform through the group so updateMatrixWorld
	// cascades to the nested meshes (measuring the standalone scene doesn't).
	const group = new Group();
	group.add(model);
	// GLTFLoader already bakes Sketchfab's Z-up→Y-up root transform, so these
	// models arrive standing up along Y — no extra rotation needed.
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

	return { group, label, back };
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
