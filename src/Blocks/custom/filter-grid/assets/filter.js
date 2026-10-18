/**
 * Client-side filtering for a Filter grid block.
 *
 * Controls are found by group (data-filter-group), anywhere on the page:
 * - Filter chips (.js-block-filter-chips): buttons with data-filter-value = a class the
 *   matching items carry (post_class: "category-testing", "region-northeast"), "" = all.
 * - Filter search (.js-block-filter-search): text matched against each item's text.
 * Both may mirror their state in a URL parameter (data-url-param) so filtered views can be
 * linked, e.g. /locations/?route=northeast. Controls render hidden and are shown here, so
 * without JavaScript the full list shows with no dead controls.
 */
const normalize = (text) => text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/\s+/g, ' ').trim();

export class FilterGrid {
	constructor({ element }) {
		this.element = element;
		this.group = element.dataset.filterGroup || 'default';
		this.items = [...element.querySelectorAll(element.dataset.itemSelector || '.wp-block-post')];
		this.empty = element.querySelector('.block-filter-grid__empty');
		this.status = element.querySelector('.block-filter-grid__status');
		this.countTemplate = element.dataset.countTemplate || '%d';
		this.chipGroups = [...document.querySelectorAll(`.js-block-filter-chips[data-filter-group="${CSS.escape(this.group)}"]`)];
		this.searches = [...document.querySelectorAll(`.js-block-filter-search[data-filter-group="${CSS.escape(this.group)}"]`)];
		this.state = { values: new Map(), query: '' };
		this.texts = new Map(this.items.map((item) => [item, normalize(item.textContent)]));
	}

	init = () => {
		if (!this.items.length) {
			return;
		}

		const params = new URLSearchParams(window.location.search);

		this.chipGroups.forEach((chips) => {
			const buttons = [...chips.querySelectorAll('[data-filter-value]')];
			const param = chips.dataset.urlParam;
			const fromUrl = param ? normalize(params.get(param) || '') : '';
			// URL value may be a term slug or its label (?route=Northeast).
			const initial = buttons.find((b) => fromUrl && (normalize(b.dataset.filterSlug || '') === fromUrl || normalize(b.textContent) === fromUrl)) || buttons[0];

			buttons.forEach((button) => button.addEventListener('click', () => {
				this.select(chips, buttons, button);
				this.apply();
				this.syncUrl(param, button.dataset.filterSlug || '');
			}));

			this.select(chips, buttons, initial);
			chips.hidden = false;
		});

		this.searches.forEach((search) => {
			const input = search.querySelector('input[type="search"]');
			const param = search.dataset.urlParam;

			if (!input) {
				return;
			}

			if (param && params.get(param)) {
				input.value = params.get(param);
			}

			const update = () => {
				this.state.query = normalize(input.value);
				this.searches.forEach((other) => {
					const otherInput = other.querySelector('input[type="search"]');
					if (otherInput && otherInput !== input) {
						otherInput.value = input.value;
					}
				});
				this.apply();
				this.syncUrl(param, input.value.trim());
			};

			input.addEventListener('input', update);
			search.addEventListener('submit', (event) => {
				event.preventDefault();
				update();
				this.element.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});

			this.state.query = normalize(input.value);
			search.hidden = false;
		});

		this.apply(true);
	};

	select = (chips, buttons, active) => {
		buttons.forEach((button) => button.setAttribute('aria-pressed', button === active ? 'true' : 'false'));
		this.state.values.set(chips, active?.dataset.filterValue || '');
	};

	matches = (item) => {
		for (const value of this.state.values.values()) {
			if (value && !item.classList.contains(value)) {
				return false;
			}
		}

		return !this.state.query || this.texts.get(item).includes(this.state.query);
	};

	apply = (initial = false) => {
		let visible = 0;

		this.items.forEach((item) => {
			const show = this.matches(item);
			item.hidden = !show;
			visible += show ? 1 : 0;
		});

		if (this.empty) {
			this.empty.hidden = visible > 0;
		}

		// Announce changes only, not the initial state.
		if (this.status && !initial) {
			this.status.textContent = this.countTemplate.replace('%d', String(visible));
		}
	};

	syncUrl = (param, value) => {
		if (!param) {
			return;
		}

		const url = new URL(window.location.href);

		if (value) {
			url.searchParams.set(param, value);
		} else {
			url.searchParams.delete(param);
		}

		window.history.replaceState(window.history.state, '', url);
	};
}
