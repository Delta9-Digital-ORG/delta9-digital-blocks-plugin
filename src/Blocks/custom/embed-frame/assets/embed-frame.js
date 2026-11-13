/**
 * Resizes embedded app frames from postMessage and optionally forwards analytics events (dataLayer
 * push plus a bubbling `embed-frame:event` DOM event with { event, params }).
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

		if (!eventMessage || data.type !== eventMessage || !data.event) {
			return;
		}

		const name = `${eventPrefix || ''}${data.event}`;

		// GTM containers read dataLayer; gtag.js-only setups (e.g. Site Kit) do not, so the event
		// is also dispatched on the frame for site code to forward.
		if (Array.isArray(window.dataLayer)) {
			window.dataLayer.push({ event: name, [eventKey || 'params']: data.params });
		}

		frame.dispatchEvent(new CustomEvent('embed-frame:event', { bubbles: true, detail: { event: name, params: data.params } }));
	};
}
