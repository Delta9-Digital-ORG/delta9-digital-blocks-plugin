/**
 * Scroll-pan the tall desktop screenshot inside its 16:9 frame (reference: index.dc.html).
 * Progress runs from 0 when the frame's top is 200px below the viewport top to 1 once the
 * frame has scrolled its own height past that point.
 */
export const initCaseStudyPan = (selector) => {
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const frames = [...document.querySelectorAll(`${selector} [data-d9-pan-frame]`)];
	if (!frames.length) {
		return;
	}

	let ticking = false;

	const update = () => {
		ticking = false;

		frames.forEach((frame) => {
			const img = frame.querySelector('[data-d9-pan-img]');
			if (!img) {
				return;
			}

			const rect = frame.getBoundingClientRect();
			const max = Math.max(0, img.offsetHeight - frame.clientHeight);
			const progress = Math.min(1, Math.max(0, (200 - rect.top) / Math.max(1, rect.height)));

			img.style.transform = `translateY(${-Math.round(progress * max)}px)`;
		});
	};

	const onScroll = () => {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(update);
		}
	};

	window.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', onScroll, { passive: true });
	frames.forEach((frame) => frame.querySelector('img')?.addEventListener('load', onScroll, { once: true }));
	update();
};
