/**
 * Shared slot geometry.
 *
 * Single source of truth for the perspective math used by three consumers:
 *   1. the three.js scene (world-space plane placement + sizing),
 *   2. the editor's static layout preview,
 *   3. the PHP fallback (mirrored in work-showcase-geometry.php).
 *
 * Keep this in lock-step with the PHP mirror — the two must produce identical
 * numbers so the no-JS <img> fallback lands where the WebGL tiles will.
 */

import slots from '../../slots.json';

const DEG2RAD = Math.PI / 180;

/**
 * World-space height visible at a given z, for the configured camera.
 * cameraZ - z is the distance from the camera to the plane.
 *
 * visibleHeightAt(z) = 2 * (cameraZ - z) * tan(fov / 2)
 */
export function visibleHeightAt(z, camera = slots.camera) {
	return 2 * (camera.z - z) * Math.tan((camera.fov * DEG2RAD) / 2);
}

/**
 * Fit a plane of the given image aspect inside a maxWidth x maxHeight box.
 * Returns world-space { width, height }.
 *
 *   h = min(maxH, maxW / aspect)
 *   w = h * aspect
 */
export function fitPlane(aspect, maxWidth, maxHeight) {
	const height = Math.min(maxHeight, maxWidth / aspect);
	const width = height * aspect;
	return { width, height };
}

/**
 * Project a slot to CSS custom-property values, expressed as multiples of
 * `1cqh` (1% of the stage height) so the fallback works at any stage aspect
 * without JS. All four values are height-relative and anchored to the stage
 * centre.
 *
 * @param {object} slot   Slot entry from slots.json.
 * @param {number} aspect Image aspect (w / h). Defaults to the slot box aspect.
 * @returns {{x:number,y:number,w:number,h:number}} cqh-relative values.
 */
export function projectSlot(slot, aspect) {
	const [x, y, z] = slot.position;
	const box = aspect || slot.maxWidth / slot.maxHeight;
	const { width, height } = fitPlane(box, slot.maxWidth, slot.maxHeight);

	const vh = visibleHeightAt(z);
	const unit = 100 / vh; // cqh per world unit at this z

	return {
		x: round(x * unit),
		y: round(-y * unit), // screen y is inverted vs world y
		w: round(width * unit),
		h: round(height * unit),
	};
}

/** Pick the slot table for the current viewport width. */
export function slotsForWidth(width) {
	return width <= slots.breakpoint ? slots.mobile : slots.desktop;
}

function round(n) {
	return Math.round(n * 1000) / 1000;
}

export { slots };
