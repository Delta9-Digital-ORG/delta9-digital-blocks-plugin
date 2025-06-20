export class TaxQueryMenuItem {
    constructor({
        containerElement,
        triggerSelector,
    }) {
        this.containerElement = containerElement;
        this.triggerSelector = triggerSelector;
    }

	init = () => {
		const item = this.containerElement;
		const trigger = item.querySelector(this.triggerSelector);

		trigger.addEventListener('click', this.togglePlay);
	};

    togglePlay = ({ target }) => {
        const item = target;

		if (!item.dataset.play) {
			return;
		}

        if (item.dataset.play === 'false') {

            this.play(item);
        } else {
		
            this.pause(item);
        }
    };

	play = (item) => {
        console.log(item);
		

	};

	pause = (item) => {
        console.log(item);
		
	};
}
