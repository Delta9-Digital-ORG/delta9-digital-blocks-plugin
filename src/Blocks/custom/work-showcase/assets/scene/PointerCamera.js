/**
 * PointerCamera — pointer → azimuth/polar lerp (ported from CameraControls.tsx,
 * without drei). Nudges the camera ±9° in azimuth and 80–100° in polar toward
 * the pointer, eased. Disabled below 480px, where the effect is noise.
 */

import { Spherical, Vector3 } from 'three';

const DEG2RAD = Math.PI / 180;
const AZIMUTH = 9 * DEG2RAD; // ± swing
const POLAR_MIN = 80 * DEG2RAD;
const POLAR_MAX = 100 * DEG2RAD;
const LERP = 0.05;
const MIN_WIDTH = 480;

export class PointerCamera {
	constructor(camera, radius, doc) {
		this.camera = camera;
		this.doc = doc;
		this.target = new Vector3(0, 0, 0);
		this.pointer = { x: 0, y: 0 }; // normalised -1..1
		this.spherical = new Spherical(radius, Math.PI / 2, 0);
		this.enabled = true;

		this._onMove = (e) => {
			const w = doc.defaultView.innerWidth;
			const h = doc.defaultView.innerHeight;
			this.pointer.x = (e.clientX / w) * 2 - 1;
			this.pointer.y = (e.clientY / h) * 2 - 1;
		};
		doc.addEventListener('pointermove', this._onMove, { passive: true });
	}

	update() {
		const enabled = this.enabled && this.doc.defaultView.innerWidth >= MIN_WIDTH;

		const targetAzimuth = enabled ? this.pointer.x * AZIMUTH : 0;
		const targetPolar = enabled
			? POLAR_MIN + ((this.pointer.y + 1) / 2) * (POLAR_MAX - POLAR_MIN)
			: Math.PI / 2;

		this.spherical.theta += (targetAzimuth - this.spherical.theta) * LERP;
		this.spherical.phi += (targetPolar - this.spherical.phi) * LERP;

		this.camera.position.setFromSpherical(this.spherical).add(this.target);
		this.camera.lookAt(this.target);
	}

	dispose() {
		this.doc.removeEventListener('pointermove', this._onMove);
	}
}
