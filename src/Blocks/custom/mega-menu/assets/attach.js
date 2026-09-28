/**
 * Move each mega panel into the core Navigation item whose link matches its trigger URL.
 *
 * Inside the <li> the panel opens on :hover / :focus-within (CSS), follows the link in tab
 * order and sits in the same fixed containing block as the reference. Items without a panel,
 * and the whole menu without JS, keep core's own dropdowns.
 */
const normalize = (href) => {
	try {
		const url = new URL(href, window.location.href);
		return `${url.origin}${url.pathname.replace(/\/?$/, '/')}`;
	} catch {
		return '';
	}
};

export const attachMegaPanels = (selector) => {
	document.querySelectorAll(selector).forEach((wrapper) => {
		const items = [...document.querySelectorAll('.wp-block-navigation-item.has-child')];

		wrapper.querySelectorAll('.d9-mega[data-d9-mega-trigger]').forEach((panel) => {
			const trigger = normalize(panel.dataset.d9MegaTrigger);
			const item = items.find((li) => normalize(li.querySelector(':scope > a')?.href || '') === trigger);

			if (!item) {
				return;
			}

			item.classList.add('d9-has-mega');
			item.appendChild(panel);

			// Escape closes the panel by returning focus to its trigger link.
			item.addEventListener('keydown', (event) => {
				if (event.key === 'Escape' && item.contains(document.activeElement)) {
					item.querySelector(':scope > a')?.focus();
					item.classList.add('d9-has-mega--closed');
				}
			});
			item.addEventListener('mouseleave', () => item.classList.remove('d9-has-mega--closed'));
			item.addEventListener('focusout', (event) => {
				if (!item.contains(event.relatedTarget)) {
					item.classList.remove('d9-has-mega--closed');
				}
			});
		});
	});
};
