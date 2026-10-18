/**
 * Stacked Cards scroll behaviour (reference `_stack()` in services/Branding.dc.html).
 *
 * Card i pins at tops[i]: pinTop for the first, then each next one just under the previous
 * card's header row (+ stackStep). Each card moves down by translateY until it reaches its pin,
 * capped so the whole stack releases together once the last card lands. Off below 860px and
 * under reduced motion.
 */
export const initStack = (section) => {
	const cards = [...section.querySelectorAll('[data-stack-card]')];
	if (cards.length < 2) {
		return;
	}

	const pinTop = parseInt(section.dataset.pinTop, 10) || 130;
	const step = parseInt(section.dataset.stackStep, 10) || 12;
	const desktop = window.matchMedia('(min-width: 861px)');
	const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
	const applied = cards.map(() => 0);
	let frame = 0;

	const reset = () => cards.forEach((card, i) => {
		applied[i] = 0;
		card.style.transform = '';
	});

	const update = () => {
		frame = 0;
		if (!desktop.matches || !motion.matches) {
			reset();
			return;
		}

		const tops = [];
		let t = pinTop;
		cards.forEach((card) => {
			tops.push(t);
			const row = card.firstElementChild;
			t += row ? row.offsetTop + row.offsetHeight + step : 108;
		});

		const nat = cards.map((card, i) => card.getBoundingClientRect().top - applied[i]);
		const last = cards.length - 1;
		cards.forEach((card, i) => {
			const cap = (tops[i] - tops[last]) + (nat[last] - nat[i]);
			const shift = Math.min(Math.max(0, tops[i] - nat[i]), cap);
			applied[i] = shift;
			card.style.transform = shift ? `translateY(${shift}px)` : '';
		});
	};

	const schedule = () => {
		if (!frame) {
			frame = window.requestAnimationFrame(update);
		}
	};

	window.addEventListener('scroll', schedule, { passive: true });
	window.addEventListener('resize', schedule);
	desktop.addEventListener('change', schedule);
	window.addEventListener('load', schedule);
	schedule();
};
