// Procedural can label. Draws the flat, unwrapped wrap for one flavor onto a
// canvas that can-scene.js maps around the label cylinder.
//
// Layout is in canvas pixels. u = 0.5 (x = W/2) is the FRONT of the can, the
// seam (x = 0 / x = W) is the back. The wrap is 2.18:1 — the circumference of
// the label band over its height — so nothing stretches when wrapped.
//
// This is a stand-in until real flat label art exists per flavor; set the
// product's `_yb_label_image` meta and the scene wraps that instead.
//
// It deliberately prints only what product data backs: brand, line, flavor
// name and the ingredients meta. Calorie/sugar claims, the drink type
// ("Sparkling Water" vs "Green Tea") and potency differ per product and
// aren't stored as fields, so they are left off rather than guessed — the
// real cans print them, and the photo/label art is where they belong.

export const LABEL_W = 2048;
export const LABEL_H = 940;

// Same faces the block's SCSS uses; the theme registers both.
const SCRIPT_FONT = '"Beverly Drive Right", "Caveat", cursive';
const SANS_FONT = '"Poppins", "Helvetica Neue", Arial, sans-serif';

/**
 * Canvas text doesn't trigger a webfont download, and drawing before the
 * face is ready bakes the fallback into the texture for good. Load both
 * faces explicitly first. Capped so a stalled font request costs the label
 * its lettering, never the whole scene.
 */
export function loadLabelFonts() {
	if (!document.fonts?.load) {
		return Promise.resolve();
	}
	const loads = Promise.all([
		document.fonts.load(`128px ${SCRIPT_FONT}`),
		document.fonts.load(`700 40px ${SANS_FONT}`),
		document.fonts.load(`500 34px ${SANS_FONT}`),
	]).catch(() => {});
	return Promise.race([loads, new Promise((r) => setTimeout(r, 2000))]);
}

export function drawLabel(flavor, canvas = document.createElement('canvas')) {
	canvas.width = LABEL_W;
	canvas.height = LABEL_H;
	const ctx = canvas.getContext('2d');
	const bg = flavor.cardBg || '#ffffff';
	const ink = flavor.nameColor || '#117571';
	const cx = LABEL_W / 2;

	ctx.fillStyle = bg;
	ctx.fillRect(0, 0, LABEL_W, LABEL_H);

	ctx.textAlign = 'center';
	ctx.textBaseline = 'middle';

	ctx.fillStyle = ink;

	// Logo badge — solid ink plate with a bg-colored keyline.
	const bw = 560;
	const bh = 270;
	const bx = cx - bw / 2;
	const by = 170;
	roundRect(ctx, bx, by, bw, bh, 46);
	ctx.fillStyle = ink;
	ctx.fill();
	roundRect(ctx, bx + 12, by + 12, bw - 24, bh - 24, 36);
	ctx.lineWidth = 5;
	ctx.strokeStyle = bg;
	ctx.stroke();

	ctx.fillStyle = bg;
	ctx.font = `700 26px ${SANS_FONT}`;
	spaced(ctx, 'ORGANICALLY GROWN', cx, by + 52, 5);
	ctx.font = `150px ${SCRIPT_FONT}`;
	fitText(ctx, 'You Betcha!', cx, by + 145, bw - 70);
	ctx.font = `700 40px ${SANS_FONT}`;
	spaced(ctx, 'CANNABIS CO', cx, by + 228, 6);

	// Product-line pill.
	const pw = 330;
	const ph = 74;
	roundRect(ctx, cx - pw / 2, 490, pw, ph, 14);
	ctx.lineWidth = 6;
	ctx.strokeStyle = ink;
	ctx.stroke();
	ctx.fillStyle = ink;
	ctx.font = `800 46px ${SANS_FONT}`;
	spaced(ctx, lineLabel(flavor.lineName), cx, 528, 4);

	// Flavor name.
	ctx.font = `128px ${SCRIPT_FONT}`;
	fitText(ctx, flavor.name || '', cx, 680, 760);

	ctx.font = `600 26px ${SANS_FONT}`;
	spaced(ctx, '12 FL OZ (355mL)', cx, 890, 2);

	// Side panels — vertical brand text, read bottom-to-top like the real can.
	[cx - 512, cx + 512].forEach((x) => {
		ctx.save();
		ctx.translate(x, LABEL_H / 2);
		ctx.rotate(-Math.PI / 2);
		ctx.font = `700 34px ${SANS_FONT}`;
		spaced(ctx, 'YOU BETCHA!  ·  CANNABIS CO', 0, 0, 6);
		ctx.restore();
	});

	// Back panel straddles the seam: draw it centred on x = 0 and again on
	// x = W so the two halves meet when wrapped.
	[0, LABEL_W].forEach((x) => drawBackPanel(ctx, flavor, x, ink));

	return canvas;
}

function drawBackPanel(ctx, flavor, x, ink) {
	ctx.fillStyle = ink;
	ctx.font = `800 30px ${SANS_FONT}`;
	spaced(ctx, 'INGREDIENTS', x, 300, 4);
	ctx.font = `500 26px ${SANS_FONT}`;
	wrapLines(ctx, flavor.ingredients || '', 360).forEach((line, i) => {
		ctx.fillText(line, x, 352 + i * 36);
	});
	ctx.font = `700 24px ${SANS_FONT}`;
	spaced(ctx, 'KEEP OUT OF REACH OF CHILDREN', x, 760, 2);
	ctx.fillText('21+', x, 800);
}

// Mood terms are stored as "Anytime" / "Daytime" / "Nighttime"; the cans
// print them as two words.
function lineLabel(mood) {
	return String(mood || 'Anytime').replace(/time$/i, ' time').trim().toUpperCase();
}

function roundRect(ctx, x, y, w, h, r) {
	ctx.beginPath();
	ctx.roundRect(x, y, w, h, r);
}

// Canvas letterSpacing is widely supported now, but fall back silently.
function spaced(ctx, text, x, y, px) {
	if ('letterSpacing' in ctx) {
		ctx.letterSpacing = `${px}px`;
		ctx.fillText(text, x + px / 2, y);
		ctx.letterSpacing = '0px';
	} else {
		ctx.fillText(text, x, y);
	}
}

// Shrinks the current font until `text` fits `maxW`.
function fitText(ctx, text, x, y, maxW) {
	const match = ctx.font.match(/(\d+)px/);
	let size = match ? parseInt(match[1], 10) : 48;
	while (size > 24 && ctx.measureText(text).width > maxW) {
		size -= 4;
		ctx.font = ctx.font.replace(/\d+px/, `${size}px`);
	}
	ctx.fillText(text, x, y);
}

function wrapLines(ctx, text, maxW) {
	const lines = [];
	let line = '';
	text.split(/\s+/).forEach((word) => {
		const next = line ? `${line} ${word}` : word;
		if (ctx.measureText(next).width > maxW && line) {
			lines.push(line);
			line = word;
		} else {
			line = next;
		}
	});
	if (line) {
		lines.push(line);
	}
	return lines;
}
