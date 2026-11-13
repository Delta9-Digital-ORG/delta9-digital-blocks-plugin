/**
 * Pure TSL helpers, ported verbatim from the reference project
 * (prag-matt-ic/imagen-header-rebuild, components/utils.ts).
 *
 * These compile to GLSL under the WebGL fallback and to WGSL under WebGPU, so
 * the same graph renders on both paths.
 */

import { Fn, vec2, vec3, mat3, float, min, sin, cos } from 'three/tsl';

/**
 * "cover" UV mapping (CSS `background-size: cover`): scales UVs about their
 * centre so a texture of `textureAspect` fills a plane of `planeAspect`,
 * cropping the overflow instead of letter-boxing.
 *
 * In this block planeAspect === imageAspect (planes are sized to the image),
 * making this a no-op — kept for robustness against future authoring.
 */
export const getCoverUv = /*#__PURE__*/ Fn(([uvCoord, planeAspect, textureAspect]) => {
	const sx = min(float(1), planeAspect.div(textureAspect));
	const sy = min(float(1), textureAspect.div(planeAspect));
	return uvCoord.sub(0.5).mul(vec2(sx, sy)).add(0.5);
});

/**
 * 3x3 rotation matrix about the Y axis (column-major, GLSL convention).
 * Used by the plane's positionNode to swing tiles in on enter.
 */
export const rotation3dY = /*#__PURE__*/ Fn(([angle]) => {
	const s = sin(angle);
	const c = cos(angle);
	return mat3(c, float(0), s.negate(), float(0), float(1), float(0), s, float(0), c);
});

/** Convenience: rotate a vec3 position about Y by `angle`. */
export const rotateY = /*#__PURE__*/ Fn(([position, angle]) => {
	return rotation3dY(angle).mul(vec3(position));
});
