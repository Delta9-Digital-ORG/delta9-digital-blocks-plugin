/**
 * Integrations hub interactions.
 *
 * Ribbon: every tick one tile swaps to the next platform (tile k shows platform
 * k, k + 4, k + 8, ...), so the cascade changes one step at a time, as in the reference.
 * Paused while the ribbon is off screen or the tab is hidden; off under reduced motion.
 *
 * Table: layer pills show only the matching rows.
 */

const TICK = 1600;

export const initRibbon = (section) => {
	const pool = section.querySelector('.d9-hub-ribbon__pool');
	const tiles = [...section.querySelectorAll('.d9-hub-ribbon__tile')];
	if (!pool || !tiles.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const items = [...pool.children];
	const shown = tiles.map((_, k) => k);
	let tick = 0;
	let timer = null;

	const step = () => {
		const k = tick % tiles.length;
		shown[k] = (shown[k] + tiles.length) % items.length;
		const next = items[shown[k]].cloneNode(true);
		next.classList.add('is-entering');
		tiles[k].replaceChildren(next);
		tick += 1;
	};

	const start = () => {
		if (!timer) {
			timer = window.setInterval(step, TICK);
		}
	};
	const stop = () => {
		window.clearInterval(timer);
		timer = null;
	};

	new IntersectionObserver(([entry]) => (entry.isIntersecting && !document.hidden ? start() : stop())).observe(section);
	document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
};

export const initTable = (section) => {
	const group = section.querySelector('.d9-hub-filters');
	if (!group) {
		return;
	}

	const buttons = [...group.querySelectorAll('.d9-hub-filter')];
	const rows = [...section.querySelectorAll('.d9-hub-table__row[data-layer]')];
	group.hidden = false;

	group.addEventListener('click', (event) => {
		const button = event.target.closest('.d9-hub-filter');
		if (!button) {
			return;
		}
		const filter = button.dataset.filter;
		buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
		rows.forEach((row) => {
			row.hidden = Boolean(filter) && row.dataset.layer !== filter;
		});
	});
};
