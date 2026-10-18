/**
 * Scroll story state: the active step (the last step whose top has passed the trigger line)
 * and the progress through it (0 to 1). Published as data attributes, CSS variables and a
 * bubbling `scroll-story:change` event; what the panel does with it is up to the theme.
 * Scroll work only runs while the block is near the viewport. With reduced motion, progress
 * is always 1, so the panel can show each step's end state without scrubbing.
 */
export class ScrollStory {
	constructor({ element }) {
		this.element = element;
		this.steps = [...element.querySelectorAll('.js-block-scroll-story-step')].filter((step) => step.closest('.js-block-scroll-story') === element);
		this.trigger = Number(element.dataset.trigger) || 0.65;
		this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		this.state = { step: -1, progress: -1 };
		this.frame = 0;
	}

	init = () => {
		if (!this.steps.length) {
			return;
		}

		this.steps.forEach((step, index) => {
			step.dataset.stepIndex = String(index);
		});
		this.element.style.setProperty('--scroll-story-count', String(this.steps.length));

		const observer = new IntersectionObserver(([entry]) => {
			if (entry.isIntersecting) {
				window.addEventListener('scroll', this.schedule, { passive: true });
				window.addEventListener('resize', this.schedule);
				this.schedule();
			} else {
				window.removeEventListener('scroll', this.schedule);
				window.removeEventListener('resize', this.schedule);
			}
		}, { rootMargin: '100% 0px' });

		observer.observe(this.element);
		this.reducedMotion.addEventListener?.('change', this.schedule);
		this.update();
	};

	schedule = () => {
		if (!this.frame) {
			this.frame = window.requestAnimationFrame(() => {
				this.frame = 0;
				this.update();
			});
		}
	};

	update = () => {
		const line = window.innerHeight * this.trigger;
		let step = 0;
		let progress = 0;

		this.steps.forEach((el, index) => {
			const rect = el.getBoundingClientRect();

			if (rect.top <= line) {
				step = index;
				progress = rect.height > 0 ? Math.min(1, Math.max(0, (line - rect.top) / rect.height)) : 1;
			}
		});

		if (this.reducedMotion.matches) {
			progress = 1;
		}

		const stepChanged = step !== this.state.step;

		// Progress in 1% steps: enough for scrubbing, without an event per pixel.
		progress = Math.round(progress * 100) / 100;

		if (!stepChanged && progress === this.state.progress) {
			return;
		}

		this.state = { step, progress };
		this.element.dataset.activeStep = String(step);
		this.element.style.setProperty('--scroll-story-step', String(step));
		this.element.style.setProperty('--scroll-story-progress', String(progress));

		if (stepChanged) {
			this.steps.forEach((el, index) => {
				let state = 'future';

				if (index < step) {
					state = 'past';
				} else if (index === step) {
					state = 'active';
				}

				el.dataset.state = state;
			});
		}

		this.element.dispatchEvent(new CustomEvent('scroll-story:change', {
			bubbles: true,
			detail: { step, progress, count: this.steps.length, stepChanged },
		}));
	};
}
