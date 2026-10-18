/**
 * WAI-ARIA tabs: builds the tab list from the panels, one panel visible at a time,
 * Left/Right/Home/End keyboard support with automatic activation.
 */
export class Tabs {
	constructor({ element, listSelector, panelSelector }) {
		this.element = element;
		this.list = element.querySelector(listSelector);
		this.panels = [...element.querySelectorAll(panelSelector)].filter((panel) => panel.closest('.js-block-tabs') === element);
		this.tabs = [];
	}

	init = () => {
		if (!this.list || !this.panels.length) {
			return;
		}

		const base = this.element.dataset.id || Math.random().toString(36).slice(2, 8);

		this.panels.forEach((panel, index) => {
			const panelId = panel.id || `tabs-${base}-panel-${index}`;
			const tabId = `tabs-${base}-tab-${index}`;
			const label = panel.dataset.tabLabel || `${index + 1}`;
			const icon = panel.dataset.tabIcon;

			const tab = document.createElement('button');
			tab.type = 'button';
			tab.id = tabId;
			tab.className = 'block-tabs__tab';
			tab.setAttribute('role', 'tab');
			tab.setAttribute('aria-controls', panelId);

			if (icon) {
				const img = document.createElement('img');
				img.src = icon;
				img.alt = '';
				img.className = 'block-tabs__tab-icon';
				tab.append(img);
			}

			const text = document.createElement('span');
			text.className = 'block-tabs__tab-label';
			text.textContent = label;
			tab.append(text);

			tab.addEventListener('click', () => this.select(index));
			tab.addEventListener('keydown', this.onKeydown);

			panel.id = panelId;
			panel.setAttribute('role', 'tabpanel');
			panel.setAttribute('aria-labelledby', tabId);
			panel.setAttribute('tabindex', '0');

			this.list.append(tab);
			this.tabs.push(tab);
		});

		this.list.hidden = false;
		this.element.dataset.tabsReady = 'true';
		this.select(0, false);
	};

	select = (index, focus = false) => {
		this.tabs.forEach((tab, i) => {
			const selected = i === index;
			tab.setAttribute('aria-selected', selected ? 'true' : 'false');
			tab.tabIndex = selected ? 0 : -1;
			this.panels[i].hidden = !selected;
		});

		if (focus) {
			this.tabs[index].focus();
		}
	};

	onKeydown = (event) => {
		const current = this.tabs.indexOf(event.currentTarget);
		const last = this.tabs.length - 1;
		const next = {
			ArrowRight: current === last ? 0 : current + 1,
			ArrowLeft: current === 0 ? last : current - 1,
			Home: 0,
			End: last,
		}[event.key];

		if (next === undefined) {
			return;
		}

		event.preventDefault();
		this.select(next, true);
	};
}
