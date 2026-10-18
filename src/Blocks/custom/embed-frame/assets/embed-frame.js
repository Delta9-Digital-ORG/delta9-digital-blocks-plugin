/**
 * Resizes embedded app frames from postMessage and optionally forwards analytics events.
 * Accepts a message only when it comes from one of the frames (event.source) and from that
 * frame's origin.
 */
export class EmbedFrame {
	constructor(frames) {
		this.frames = frames.map((frame) => {
			let origin = '';
			try {
				origin = new URL(frame.src, window.location.href).origin;
			} catch (e) {
				origin = '';
			}
			return { frame, origin };
		});
	}

	init = () => {
		window.addEventListener('message', this.onMessage);
	};

	onMessage = (event) => {
		const match = this.frames.find(({ frame, origin }) => frame.contentWindow === event.source && origin === event.origin);

		if (!match) {
			return;
		}

		const { frame } = match;
		const data = event.data || {};
		const { resizeMessage, eventMessage, eventPrefix, eventKey } = frame.dataset;

		if (resizeMessage && data.type === resizeMessage && typeof data.height === 'number' && data.height > 0) {
			frame.style.height = `${Math.ceil(data.height)}px`;
			return;
		}

		if (eventMessage && data.type === eventMessage && data.event && Array.isArray(window.dataLayer)) {
			window.dataLayer.push({ event: `${eventPrefix || ''}${data.event}`, [eventKey || 'params']: data.params });
		}
	};
}
