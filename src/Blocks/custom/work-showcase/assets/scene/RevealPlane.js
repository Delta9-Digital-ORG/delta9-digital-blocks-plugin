/**
 * RevealPlane — one textured tile.
 *
 * Node graph (ported from the reference ImageRevealPlane.tsx):
 *   cover UV -> texture -> optional hashBlur -> rounded-rect SDF reveal mask
 *   positionNode: rotate + translate on enter, translate up on scroll exit
 *
 * Deliberate changes from the reference (per the build plan):
 *  - `blurred` is a build-time branch, not a runtime uniform: a sharp tile
 *    never compiles the blur loop.
 *  - hashBlur uses { repeats: 24 } (not 96) and blurred tiles are fed the
 *    smaller WP "medium" image.
 *  - plane height derives from the image aspect (portrait cans, wide logos),
 *    so planeAspect === imageAspect and the cover-UV path is a no-op.
 */

import { PlaneGeometry, Mesh } from 'three';
import { MeshBasicNodeMaterial } from 'three/webgpu';
import {
	Fn, uv, texture, uniform, positionLocal,
	float, vec2, vec3, abs, min, max, length, smoothstep,
} from 'three/tsl';
import { hashBlur } from 'three/examples/jsm/tsl/display/hashBlur.js';
import { getCoverUv, rotation3dY } from './tsl.js';

const ENTER_ROTATION = 0.6; // radians the tile swings through on enter
const ENTER_OFFSET = 0.8; // world units the tile slides in from
const EXIT_DISTANCE = 3.0; // world units the tile travels up on scroll exit

/** Signed-distance to a rounded box centred at the origin (2D, uv space). */
const sdRoundBox = /*#__PURE__*/ Fn(([p, b, r]) => {
	const q = abs(p).sub(b).add(r);
	return length(max(q, vec2(0))).add(min(max(q.x, q.y), float(0))).sub(r);
});

export class RevealPlane {
	/**
	 * @param {object}  opts
	 * @param {THREE.Texture} opts.map         Tile texture.
	 * @param {number}  opts.width             Plane width (world units).
	 * @param {number}  opts.height            Plane height (world units).
	 * @param {number}  opts.imageAspect       Texture aspect (w / h).
	 * @param {boolean} opts.blurred           Compile the blur branch.
	 * @param {number}  opts.radius            Corner radius (0–0.2, uv space).
	 * @param {number[]} opts.position         [x, y, z] world position.
	 */
	constructor({ map, width, height, imageAspect, blurred, radius, position }) {
		this.uReveal = uniform(0);
		this.uEnterProgress = uniform(0);
		this.uExitProgress = uniform(0);
		this.uRadius = uniform(radius);

		const material = new MeshBasicNodeMaterial({ transparent: true });
		const planeAspect = float(width / height);

		// --- colour: cover UV -> texture (-> hashBlur when blurred) ---------
		const coverUv = getCoverUv(uv(), planeAspect, float(imageAspect));
		const texNode = texture(map, coverUv);
		const rgb = blurred ? hashBlur(texNode, float(0.06), { repeats: 24 }) : texNode;
		material.colorNode = rgb.rgb;

		// --- reveal mask: rounded-rect SDF growing with uReveal -------------
		const p = uv().sub(0.5);
		const halfSize = vec2(0.5, 0.5).mul(this.uReveal);
		const d = sdRoundBox(p, halfSize, this.uRadius);
		const mask = float(1).sub(smoothstep(float(-0.004), float(0.004), d));
		material.opacityNode = mask.mul(texNode.a);

		// --- position: rotate/translate on enter, translate up on exit ------
		material.positionNode = Fn(() => {
			const enter = this.uEnterProgress;
			const rest = float(1).sub(enter);
			const rotated = rotation3dY(rest.mul(ENTER_ROTATION)).mul(positionLocal);
			const entered = rotated.add(vec3(float(0), float(0), rest.mul(ENTER_OFFSET)));
			return entered.add(vec3(float(0), this.uExitProgress.mul(EXIT_DISTANCE), float(0)));
		})();

		this.material = material;
		this.mesh = new Mesh(new PlaneGeometry(width, height, 1, 1), material);
		this.mesh.position.set(position[0], position[1], position[2]);
	}

	/** Reveal progress 0 -> 1 (drives the SDF mask). */
	setReveal(v) {
		this.uReveal.value = v;
	}

	/** Enter progress 0 -> 1 (drives the intro rotate/slide). */
	setEnter(v) {
		this.uEnterProgress.value = v;
	}

	/** Exit progress 0 -> 1 (drives the scroll-out translate). */
	setExit(v) {
		this.uExitProgress.value = v;
	}

	dispose() {
		this.mesh.geometry.dispose();
		this.material.dispose();
		const map = this.material.colorNode;
		if (map?.value?.dispose) {
			map.value.dispose();
		}
	}
}
