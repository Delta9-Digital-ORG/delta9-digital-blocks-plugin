/**
 * Post hero share menu (handoff design_handoff_blog_post, section 4): the button toggles the
 * menu; "Copy link" writes the URL to the clipboard and shows "Link copied" for 1.4s, then
 * closes; clicking outside or pressing Escape closes it.
 */
export const initShareMenus = (selector) => {
	document.querySelectorAll(selector).forEach((share) => {
		const toggle = share.querySelector('.d9-share__toggle');
		const menu = share.querySelector('.d9-share__menu');
		const copy = share.querySelector('.d9-share__copy');

		if (!toggle || !menu) {
			return;
		}

		const setOpen = (open) => {
			share.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', String(open));
		};

		toggle.hidden = false;
		share.classList.add('is-ready');

		toggle.addEventListener('click', () => setOpen(!share.classList.contains('is-open')));

		document.addEventListener('click', (event) => {
			if (!share.contains(event.target)) {
				setOpen(false);
			}
		});

		share.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && share.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});

		copy?.addEventListener('click', async () => {
			const label = copy.querySelector('.d9-share__label');
			const original = label.textContent;

			try {
				await navigator.clipboard.writeText(copy.dataset.url || window.location.href);
				label.textContent = copy.dataset.copied;
			} catch {
				window.prompt('', copy.dataset.url || window.location.href); // eslint-disable-line no-alert
			}

			window.setTimeout(() => {
				label.textContent = original;
				setOpen(false);
			}, 1400);
		});
	});
};
