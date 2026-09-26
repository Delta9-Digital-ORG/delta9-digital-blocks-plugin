/**
 * Scene — vanilla three.js orchestrator (no R3F).
 *
 * WordPress ships React 18 as wp-element; R3F 9 needs React 19, so we run
 * vanilla three and mount the same class in both the front end and the editor.
 * WebGPU with { forceWebGL: true } falls back to the identical GLSL path when
 * WebGPU is unavailable, rather than a "not supported" screen.
 *
 * Every window/document reference goes through container.ownerDocument so the
 * exact same class runs inside the editor iframe.
 */

import { Scene as ThreeScene, PerspectiveCamera, TextureLoader, SRGBColorSpace, Group, Raycaster, Vector2 } from 'three';
import { WebGPURenderer } from 'three/webgpu';
import WebGPU from 'three/examples/jsm/capabilities/WebGPU.js';
import { RevealPlane } from './RevealPlane.js';
import { PointerCamera } from './PointerCamera.js';
import { buildIntro, buildExit } from './timeline.js';
import { slots as slotConfig, slotsForWidth, fitPlane } from './layout.js';

export class Scene {
	/**
	 * @param {object}  opts
	 * @param {HTMLElement} opts.container  Stage element (owns the canvas).
	 * @param {Array}   opts.tiles          Resolved tile data (see view.js).
	 * @param {boolean} opts.scrollExit     Enable scroll-out (false in editor).
	 * @param {number}  opts.radius         Corner radius (0–0.2).
	 */
	constructor({ container, tiles, scrollExit = true, radius = 0.08 }) {
		this.container = container;
		this.doc = container.ownerDocument;
		this.win = this.doc.defaultView;
		this.tiles = tiles;
		this.scrollExit = scrollExit;
		this.radius = radius;
		this.planes = [];
		this.disposed = false;
		this.lastTime = 0;
	}

	async mount() {
		const { camera } = slotConfig;

		this.canvas = this.doc.createElement('canvas');
		this.canvas.className = 'd9-showcase__canvas';
		this.container.appendChild(this.canvas);

		this.renderer = new WebGPURenderer({
			canvas: this.canvas,
			antialias: true,
			alpha: true,
			forceWebGL: !WebGPU.isAvailable(),
		});
		await this.renderer.init();

		this.container.setAttribute(
			'data-d9-backend',
			this.renderer.backend?.isWebGPUBackend ? 'webgpu' : 'webgl',
		);

		this.scene = new ThreeScene();
		this.group = new Group();
		this.scene.add(this.group);

		this.camera = new PerspectiveCamera(camera.fov, 1, 0.1, 100);
		this.camera.position.set(0, 0, camera.z);
		this.pointerCamera = new PointerCamera(this.camera, camera.z, this.doc);

		await this._buildPlanes();
		this._resize();

		this._resizeObserver = new this.win.ResizeObserver(() => this._resize());
		this._resizeObserver.observe(this.container);

		this.intro = buildIntro(this.planes, this._activeSlots);
		this.exit = buildExit(this.planes, this.container, this.scrollExit);

		this._enableInteraction();

		this.resume();
		return this;
	}

	/** Resume the render loop (e.g. when the block scrolls back into view). */
	resume() {
		if (!this.disposed) {
			this.renderer?.setAnimationLoop((time) => this._render(time));
		}
	}

	/** Pause the render loop (e.g. when the block scrolls offscreen). */
	pause() {
		this.renderer?.setAnimationLoop(null);
	}

	/**
	 * Raycaster hover + click-through so tile links still work in 3D mode.
	 * A caption chip follows the pointer; a click navigates the tile's link.
	 */
	_enableInteraction() {
		const interactive = this.tiles.some((t) => t.link || t.caption);
		if (!interactive) {
			return;
		}

		this.raycaster = new Raycaster();
		this._ndc = new Vector2();
		this._hover = -1;
		this.chip = this.doc.createElement('div');
		this.chip.className = 'd9-showcase__chip';
		this.chip.hidden = true;
		this.container.appendChild(this.chip);

		const meshes = this.planes.map((p) => p.mesh);

		this._onPointerMove = (e) => {
			const rect = this.canvas.getBoundingClientRect();
			this._ndc.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
			this._ndc.y = -(((e.clientY - rect.top) / rect.height) * 2 - 1);
			this.raycaster.setFromCamera(this._ndc, this.camera);
			const hit = this.raycaster.intersectObjects(meshes, false)[0];
			this._hover = hit ? meshes.indexOf(hit.object) : -1;

			const tile = this._hover > -1 ? this.tiles[this._hover] : null;
			if (tile && (tile.caption || tile.link)) {
				this.chip.textContent = tile.caption || '';
				this.chip.hidden = !tile.caption;
				this.chip.style.left = `${e.clientX - rect.left}px`;
				this.chip.style.top = `${e.clientY - rect.top}px`;
				this.canvas.style.cursor = tile.link ? 'pointer' : 'default';
			} else {
				this.chip.hidden = true;
				this.canvas.style.cursor = 'default';
			}
		};

		this._onPointerDown = () => {
			const tile = this._hover > -1 ? this.tiles[this._hover] : null;
			if (tile?.link) {
				this.win.location.href = tile.link;
			}
		};

		this.canvas.addEventListener('pointermove', this._onPointerMove, { passive: true });
		this.canvas.addEventListener('pointerdown', this._onPointerDown);
	}

	async _buildPlanes() {
		const loader = new TextureLoader();
		this._activeSlots = slotsForWidth(this.win.innerWidth);

		const count = Math.min(this._activeSlots.length, this.tiles.length);
		for (let i = 0; i < count; i++) {
			const slot = this._activeSlots[i];
			const tile = this.tiles[i];
			// Blurred tiles are fed the smaller "medium" image (cheaper, blur hides it).
			const src = slot.blurred && tile.mediumSrc ? tile.mediumSrc : tile.src;

			// eslint-disable-next-line no-await-in-loop
			const map = await loader.loadAsync(src);
			map.colorSpace = SRGBColorSpace;

			const imageAspect = tile.aspect || map.image.width / map.image.height;
			const { width, height } = fitPlane(imageAspect, slot.maxWidth, slot.maxHeight);

			const plane = new RevealPlane({
				map,
				width,
				height,
				imageAspect,
				blurred: slot.blurred,
				radius: this.radius,
				position: slot.position,
			});
			this.group.add(plane.mesh);
			this.planes.push(plane);
		}
	}

	_resize() {
		const w = this.container.clientWidth;
		const h = this.container.clientHeight;
		if (!w || !h) {
			return;
		}
		this.renderer.setPixelRatio(Math.min(this.win.devicePixelRatio, 2));
		this.renderer.setSize(w, h, false);
		this.camera.aspect = w / h;
		this.camera.updateProjectionMatrix();
	}

	_render(time) {
		if (this.disposed) {
			return;
		}
		this.lastTime = time; // delta available if needed; THREE.Clock is deprecated in 0.186
		this.pointerCamera.update();
		this.renderer.render(this.scene, this.camera);
	}

	dispose() {
		this.disposed = true;
		this.renderer?.setAnimationLoop(null);
		this._resizeObserver?.disconnect();
		this.exit?.kill();
		this.intro?.kill();
		this.pointerCamera?.dispose();
		if (this._onPointerMove) {
			this.canvas.removeEventListener('pointermove', this._onPointerMove);
			this.canvas.removeEventListener('pointerdown', this._onPointerDown);
		}
		this.chip?.remove();
		this.planes.forEach((p) => p.dispose());
		this.planes = [];
		this.renderer?.dispose();
		this.canvas?.remove();
	}
}

export default Scene;
