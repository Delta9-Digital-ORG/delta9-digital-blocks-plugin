/**
 * GSAP timelines for the scene.
 *
 *  - buildIntro:  per-plane reveal + enter stagger, driven by each slot's delay.
 *  - buildExit:   ScrollTrigger that pushes tiles up as the *block* scrolls out
 *                 of view (top top -> bottom top) — it's a section, not a page,
 *                 so we scope to the block's own bounds rather than end:'max'.
 */

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * @param {RevealPlane[]} planes
 * @param {object[]}      slots   Matching slot entries (for per-tile delay).
 * @returns {gsap.core.Timeline}
 */
export function buildIntro(planes, slots) {
	const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: 1.1 } });

	planes.forEach((plane, i) => {
		const delay = slots[i]?.delay ?? 0;
		const proxy = { reveal: 0, enter: 0 };
		tl.to(
			proxy,
			{
				reveal: 1,
				enter: 1,
				onUpdate: () => {
					plane.setReveal(proxy.reveal);
					plane.setEnter(proxy.enter);
				},
			},
			delay,
		);
	});

	return tl;
}

/**
 * @param {RevealPlane[]} planes
 * @param {HTMLElement}   trigger  The block root element.
 * @param {boolean}       enabled  Scroll exit on/off (off inside the editor).
 * @returns {ScrollTrigger|null}
 */
export function buildExit(planes, trigger, enabled = true) {
	if (!enabled) {
		return null;
	}

	const proxy = { exit: 0 };
	const st = ScrollTrigger.create({
		trigger,
		start: 'top top',
		end: 'bottom top',
		scrub: true,
		onUpdate: (self) => {
			proxy.exit = self.progress;
			planes.forEach((plane) => plane.setExit(proxy.exit));
		},
	});

	return st;
}
