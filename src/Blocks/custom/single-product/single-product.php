<?php

/**
 * Template for the Single Product Block.
 *
 * Phase 3 — pulls the current product + its top-level product_cat siblings
 * from WooCommerce, reads brand-color + custom-field meta, then seeds the
 * Interactivity API state via wp_interactivity_state().
 *
 * @package Delta9DigitalBlocksPlugin
 */

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$product = wc_get_product( get_the_ID() );
if ( ! $product instanceof \WC_Product ) {
	return;
}

/**
 * Optional explicit sibling override from the block attribute. Empty array
 * (default) → use category-based discovery.
 *
 * @var int[] $explicit_ids
 */
$explicit_ids = $attributes['singleProductExplicitIds'] ?? [];

// Top-cat resolution, sibling discovery, and flavor building live in
// SingleProductData so the editor-preview REST route
// (SingleProductPreviewRoute) returns the exact same shape. Keys mirror
// the fixture shape from _mockups/single-product/app.js.
$state = \Delta9DigitalBlocksPlugin\SingleProduct\SingleProductData::getState( $product, $explicit_ids );

if ( ! $state ) {
	return;
}

$flavors = $state['flavors'];
$active  = $state['active'];

// Seed the Interactivity API state on the server. The view script reads it via
// `import { store } from '@wordpress/interactivity'` and shares the same
// namespace key ('delta9/singleProduct').
// Initial qty matches the first pack's quantity so the picker and the
// qty stepper start in sync. Falls back to 1 if the product has no packs.
$initial_qty = ! empty( $active['packOptions'][0]['quantity'] )
	? (int) $active['packOptions'][0]['quantity']
	: 1;

wp_interactivity_state(
	'delta9/singleProduct',
	[
		'activeId'       => $active['id'],
		'qty'            => $initial_qty,
		'tab'            => 'description',
		'packIndex'      => 0,
		'currencySymbol' => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
		'flavors'        => $flavors,
	]
);

// Make sure the iAPI script module + import map are emitted on this page.
wp_enqueue_script_module( '@wordpress/interactivity' );

/**
 * Resolve the award badge list. Accepts a new `singleProductBadges` array
 * attribute of `[ [ 'url' => …, 'alt' => … ], … ]` and falls back to the
 * legacy single `singleProductBadgeUrl` / `singleProductBadgeAlt` pair.
 *
 * There is deliberately no default: awards are per product now, and most
 * products have not won one. A product with no badge set shows no badge —
 * printing the shipped SVG on all of them would claim an award every product
 * has not got.
 *
 * Rows with no image are dropped too, so a repeater row an editor added but
 * never filled prints nothing rather than a placeholder.
 */
$badge_alt = $attributes['singleProductBadgeAlt'] ?? __( 'Award badge', 'delta9-digital-blocks-plugin' );
$badge_url = $attributes['singleProductBadgeUrl'] ?? '';

$badges_attr = $attributes['singleProductBadges'] ?? [];
$badges = [];
if ( is_array( $badges_attr ) && ! empty( $badges_attr ) ) {
	foreach ( $badges_attr as $b ) {
		if ( ! is_array( $b ) || empty( $b['url'] ) ) {
			continue;
		}
		$badges[] = [
			'url' => (string) $b['url'],
			'alt' => (string) ( $b['alt'] ?? $badge_alt ),
		];
	}
} elseif ( $badge_url ) {
	$badges[] = [ 'url' => $badge_url, 'alt' => $badge_alt ];
}

$initial_style = sprintf(
	'--page-bg: %s; --page-contrast: %s; --name-color: %s;',
	esc_attr( $active['pageBg'] ?: '#fff' ),
	esc_attr( $active['pageContrast'] ?: '#117571' ),
	esc_attr( $active['nameColor'] ?: '#117571' )
);

// Picks the color a flavor card's label is printed in.
//
// The label sits on white, and some flavors pair a deep card background with a
// pale name color (Blood Orange Lime's #D3E5AD, Blackberry Lemon's #FFF450).
// Those read fine on the can but wash out completely on the white label, so for
// them the card background — the saturated half of the same brand pair — is
// used instead. The brand hexes stay untouched in product meta either way.
//
// A closure rather than a named function: this template is included once per
// block instance, and a named declaration would fatal on the second render.
$yb_label_color = static function ( $name_color, $card_bg ) {
	$hex = ltrim( (string) $name_color, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) ) {
		return $name_color;
	}

	// Rec. 709 luma. Above ~70% the color stops reading against white.
	$luma = (
		0.2126 * hexdec( substr( $hex, 0, 2 ) )
		+ 0.7152 * hexdec( substr( $hex, 2, 2 ) )
		+ 0.0722 * hexdec( substr( $hex, 4, 2 ) )
	) / 255;

	if ( $luma <= 0.7 ) {
		return $name_color;
	}

	return $card_bg ?: $name_color;
};

?>
<section
	class="yb-single-product alignfull"
	data-wp-interactive="delta9/singleProduct"
	data-wp-init="callbacks.applyFlavorVars"
	data-wp-watch="callbacks.applyFlavorVars"
	style="<?php echo $initial_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — esc_attr applied inline above. ?>"
>

	<div class="yb-single-product__inner">

	<div class="yb-single-product__hero">

		<div class="yb-single-product__flavors">
			<?php foreach ( $flavors as $flavor ) : ?>
				<button
					type="button"
					class="yb-single-product__flavorCard <?php echo $flavor['id'] === $active['id'] ? 'is-active' : ''; ?>"
					data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'id' => $flavor['id'] ] ) ); ?>'
					data-wp-on--click="actions.selectFlavor"
					style="--flavor-bg: <?php echo esc_attr( $flavor['cardBg'] ?: '#ccc' ); ?>; --flavor-name: <?php echo esc_attr( $yb_label_color( $flavor['nameColor'] ?: '#117571', $flavor['cardBg'] ) ); ?>;"
				>
					<div class="yb-single-product__flavorCard__image">
						<img src="<?php echo esc_url( $flavor['cardImage'] ); ?>" alt="" />
					</div>
					<span class="yb-single-product__flavorCard__label">
						<?php echo esc_html( $flavor['cardLabel'] ); ?>
					</span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="yb-single-product__heroImage">
			<img
				data-wp-bind--src="state.activeFlavor.image"
				data-wp-bind--alt="state.activeFlavor.name"
				src="<?php echo esc_url( $active['image'] ); ?>"
				alt="<?php echo esc_attr( $active['name'] ); ?>"
			/>
		</div>

		<div class="yb-single-product__panel">
			<div class="yb-single-product__tabs">
				<button
					type="button"
					class="is-active"
					data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'tab' => 'description' ] ) ); ?>'
					data-wp-on--click="actions.selectTab"
				><?php esc_html_e( 'Description', 'delta9-digital-blocks-plugin' ); ?></button>

				<button
					type="button"
					data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'tab' => 'cannafacts' ] ) ); ?>'
					data-wp-on--click="actions.selectTab"
				><?php esc_html_e( 'Nutritional facts', 'delta9-digital-blocks-plugin' ); ?></button>

				<button
					type="button"
					data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'tab' => 'benefits' ] ) ); ?>'
					data-wp-on--click="actions.selectTab"
				><?php esc_html_e( 'Benefits', 'delta9-digital-blocks-plugin' ); ?></button>
			</div>

			<div class="yb-single-product__panelCard">
				<?php
				// No heading in the panel — the tab button above already names
				// the section. The description's opening line used to be split
				// out into that heading, so it is printed here with the rest of
				// the copy; dropping the heading without this would lose it.
				?>
				<div class="yb-single-product__panelCard__body" data-wp-text="state.panelBody"><?php echo esc_html( (string) $active['description'] ); ?></div>
				<?php
				// The FDA panel is real markup, not part of the panel body:
				// that body is bound with data-wp-text, which writes
				// textContent, so any table markup folded into the string
				// would render as escaped source. Only the tab toggle needs
				// the Interactivity API here — picking a flavor navigates to
				// that product's own page, so the active flavor (and this
				// table) is fixed for the life of the page and can be
				// rendered entirely on the server.
				//
				// Same renderer as the description row's nutrition card, so
				// the two copies of this grid can't drift apart.
				echo \Delta9DigitalBlocksPlugin\SingleProduct\NutritionFactsTable::render(
					$active['nutritionFacts'],
					'yb-single-product__nfTable',
					'data-wp-bind--hidden="state.nutritionHidden" hidden'
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — renderer escapes labels and values.
				?>
			</div>
		</div>

	</div>

	<div class="yb-single-product__purchase">
		<div class="yb-single-product__purchaseTop">
			<div class="yb-single-product__awards">
				<?php
				foreach ( $badges as $badge ) :
					// Badges are inlined rather than linked with <img> wherever
					// the source is a local SVG, because an <img> is an opaque
					// document: it cannot see --page-contrast, so the badge
					// would keep the colour it was drawn in while the rest of
					// the page retints per flavor. Inlined, the SCSS can repaint
					// its paths. Anything else (PNG/JPG, or a remote file) still
					// renders as an image.
					$badge_svg = '';
					$attachment_id = attachment_url_to_postid( $badge['url'] );

					if ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
						$file = get_attached_file( $attachment_id );

						if ( $file && file_exists( $file ) ) {
							$badge_svg = $file;
						}
					}
					?>
					<?php if ( $badge_svg ) : ?>
						<div class="yb-single-product__award" role="img" aria-label="<?php echo esc_attr( $badge['alt'] ); ?>">
							<?php
							readfile( $badge_svg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — SVG is either shipped by the plugin or an uploaded attachment already sanitized by safe-svg.
							?>
						</div>
					<?php else : ?>
						<img class="yb-single-product__award" src="<?php echo esc_url( $badge['url'] ); ?>" alt="<?php echo esc_attr( $badge['alt'] ); ?>" />
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<div class="yb-single-product__buyCard">
			<div class="yb-single-product__buyTop">
				<div>
					<div class="yb-single-product__stars">
						<span>
							<?php
							/* translators: 1: average star rating, 2: review count. */
							printf(
								esc_html__( '(%1$s stars) • %2$s reviews', 'delta9-digital-blocks-plugin' ),
								esc_html( (string) $active['starsAvg'] ),
								esc_html( (string) $active['reviewCount'] )
							);
							?>
						</span>
					</div>
					<h2 data-wp-text="state.activeFlavor.name"><?php echo esc_html( $active['name'] ); ?></h2>
				</div>
				<div class="yb-single-product__price">
					<strong data-wp-text="state.displayPrice"><?php echo esc_html( $active['packOptions'][0]['priceHtml'] ?? $active['priceHtml'] ); ?></strong>
					<?php if ( $active['mood'] ) : ?>
						<span class="yb-single-product__mood"><?php echo esc_html( $active['mood'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			<?php $has_packs = ! empty( $active['packOptions'] ); ?>
			<div class="yb-single-product__sizePickerWrap" style="<?php echo $has_packs ? '' : 'display:none;'; ?>">
				<select
					class="yb-single-product__sizePicker"
					data-wp-on--change="actions.selectPack"
					aria-label="<?php esc_attr_e( 'Pack size', 'delta9-digital-blocks-plugin' ); ?>"
				>
					<?php if ( $has_packs ) : ?>
						<?php foreach ( $active['packOptions'] as $idx => $opt ) : ?>
							<option value="<?php echo (int) $idx; ?>"><?php echo esc_html( sprintf( '%s — %s', $opt['label'], $opt['priceHtml'] ) ); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<div class="yb-single-product__buyBottom">
				<div class="yb-single-product__qty">
					<button type="button" data-wp-on--click="actions.decrementQty" aria-label="<?php esc_attr_e( 'Decrease quantity', 'delta9-digital-blocks-plugin' ); ?>">−</button>
					<input
						type="number"
						min="1"
						step="1"
						class="yb-single-product__qtyInput"
						data-wp-bind--value="state.qty"
						data-wp-on--input="actions.setQty"
						aria-label="<?php esc_attr_e( 'Quantity', 'delta9-digital-blocks-plugin' ); ?>"
						value="<?php echo (int) $initial_qty; ?>"
					/>
					<button type="button" data-wp-on--click="actions.incrementQty" aria-label="<?php esc_attr_e( 'Increase quantity', 'delta9-digital-blocks-plugin' ); ?>">+</button>
				</div>
				<button type="button" class="yb-single-product__addToCart" data-wp-on--click="actions.addToCart"><?php esc_html_e( 'Add To Cart', 'delta9-digital-blocks-plugin' ); ?></button>
			</div>
		</div>
		</div><!-- /.yb-single-product__purchaseTop -->

		<div class="yb-single-product__wordmark" data-wp-text="state.wordmarkName"><?php
			// Matches the `wordmarkName` getter below so the server paint and
			// the hydrated value break identically. U+2060 is a word joiner:
			// zero-width, and it cancels the wrap opportunity a hyphen creates.
			echo esc_html( str_replace( '-', "-\u{2060}", $active['name'] ) );
		?></div>
	</div>

	</div><!-- /.yb-single-product__inner -->

</section>

<script type="module">
import { store, getContext } from '@wordpress/interactivity';

// Helpers used inside actions. Defined as module-scope functions so
// they can share access to the `state` proxy returned by store().
function rebuildSizePicker( root, flavor ) {
	const wrap = root.querySelector( '.yb-single-product__sizePickerWrap' );
	const select = root.querySelector( '.yb-single-product__sizePicker' );
	if ( ! wrap || ! select ) return;
	if ( ! flavor.packOptions || flavor.packOptions.length === 0 ) {
		wrap.style.display = 'none';
		select.innerHTML = '';
		return;
	}
	wrap.style.display = '';
	select.innerHTML = flavor.packOptions
		.map( ( p, i ) => '<option value="' + i + '">' + p.label + ' — ' + p.priceHtml + '</option>' )
		.join( '' );
	select.selectedIndex = 0;
}

function snapPackToQty( s ) {
	const f = s.activeFlavor;
	if ( ! f || ! f.packOptions || f.packOptions.length === 0 ) return;
	const idx = f.packOptions.findIndex( ( p ) => p.quantity === s.qty );
	if ( idx >= 0 ) {
		s.packIndex = idx;
		const sel = document.querySelector( '.yb-single-product__sizePicker' );
		if ( sel ) sel.selectedIndex = idx;
	}
}

// --- Cumulative "step" pricing (mirrors PackPricing.php on the server) ---
// Base per-unit price for units beyond the active tier: the single-unit
// (quantity === 1) tier price, else the smallest tier's implied per-unit.
function packBaseUnit( flavor ) {
	const opts = ( flavor?.packOptions ) || [];
	const one = opts.find( ( o ) => o.quantity === 1 );
	if ( one ) return one.price;
	const first = opts[ 0 ];
	return first?.quantity > 0 ? first.price / first.quantity : 0;
}

// Largest tier whose quantity is <= qty (order-independent).
function packActiveTier( flavor, qty ) {
	const opts = ( flavor?.packOptions ) || [];
	let active = null;
	for ( const o of opts ) {
		if ( o.quantity <= qty ) {
			if ( ! active || o.quantity > active.quantity ) {
				active = o;
			}
		}
	}
	return active;
}

// tier.price + remaining units at base; no tier → qty * base.
function computePackTotal( flavor, qty ) {
	const opts = ( flavor?.packOptions ) || [];
	if ( ! opts.length || qty < 1 ) return 0;
	const base = packBaseUnit( flavor );
	let total = 0;
	let remaining = qty;
	// Greedily stack the largest tier that fits, so the discount keeps applying
	// past the top tier (e.g. 15 = 48-pack + 12-pack); leftover below the
	// smallest tier bills at base.
	while ( remaining > 0 ) {
		const tier = packActiveTier( flavor, remaining );
		if ( ! tier ) { total += remaining * base; break; }
		total += tier.price;
		remaining -= tier.quantity;
	}
	return total;
}

const { state } = store( 'delta9/singleProduct', {
	state: {
		get activeFlavor() {
			return state.flavors.find( ( f ) => f.id === state.activeId );
		},
		get activePack() {
			const f = state.activeFlavor;
			if ( ! f || ! f.packOptions || f.packOptions.length === 0 ) return null;
			return f.packOptions[ state.packIndex ] || f.packOptions[ 0 ] || null;
		},
		// Name for the script wordmark, which wraps on spaces only. A hyphen
		// normally hands the browser a wrap opportunity, so "Sour-Lime" gets
		// split across lines. A word joiner after each hyphen removes that
		// opportunity — it is zero-width and needs no glyph, so the hyphen
		// still renders normally in the brand script face.
		get wordmarkName() {
			const f = state.activeFlavor;
			return f ? String( f.name ).replace( /-/g, '-\u2060' ) : '';
		},
		get displayPrice() {
			const f = state.activeFlavor;
			if ( f?.packOptions?.length ) {
				const sym = state.currencySymbol || '$';
				return sym + computePackTotal( f, state.qty ).toFixed( 2 );
			}
			return f ? f.priceHtml : '';
		},
		// External Product Gallery Slot block reads these via
		// data-wp-context='{"slotIndex":N}'. Empty string when the slot
		// index is past the active flavor's gallery length — the binding
		// will just clear the img and the figure renders empty.
		get slotImageSrc() {
			const ctx = getContext();
			const f = state.activeFlavor;
			const idx = ( typeof ctx?.slotIndex === 'number' ) ? ctx.slotIndex : 0;
			return ( f?.gallery?.[ idx ] ) ? f.gallery[ idx ].src : '';
		},
		get slotImageAlt() {
			const ctx = getContext();
			const f = state.activeFlavor;
			const idx = ( typeof ctx?.slotIndex === 'number' ) ? ctx.slotIndex : 0;
			return ( f?.gallery?.[ idx ] ) ? f.gallery[ idx ].alt : '';
		},
		// The panel has no heading — the tab button names the section. The
		// description is printed whole rather than having its opening line
		// lifted into a heading, which is where that line used to go.
		get panelBody() {
			const f = state.activeFlavor;
			if ( ! f ) return '';
			if ( state.tab === 'description' ) return f.description || '';
			// Nothing for the nutrition tab: that panel is the FDA table
			// below, and the serving-size copy that used to sit above it is
			// already printed in the description row's nutrition card.
			if ( state.tab === 'benefits' ) return f.benefits || '';
			return '';
		},
		// Drives the `hidden` attribute on the server-rendered nutrition
		// panel, which only belongs to the "Nutritional facts" tab. Phrased
		// as "hidden" rather than "visible" so the binding needs no negation.
		get nutritionHidden() {
			return state.tab !== 'cannafacts';
		},
	},
	actions: {
		selectFlavor() {
			// Just navigate to the selected flavor's product page. Every
			// block on that page (slot block, WC core blocks, related
			// products, custom details) re-renders server-side against
			// the new product, so we don't have to keep N iAPI bindings
			// in sync across the page.
			const { id } = getContext();
			const f = state.flavors.find( ( x ) => x.id === id );
			if ( f?.permalink ) {
				window.location.href = f.permalink;
			}
		},
		selectPack( event ) {
			const idx = parseInt( event.target.value, 10 ) || 0;
			state.packIndex = idx;
			const f = state.activeFlavor;
			if ( f?.packOptions?.[ idx ] ) {
				state.qty = f.packOptions[ idx ].quantity;
			}
		},
		selectTab() {
			const { tab } = getContext();
			state.tab = tab;
			document.querySelectorAll( '.yb-single-product__tabs button' ).forEach( ( b ) => {
				b.classList.remove( 'is-active' );
				const ctx = b.getAttribute( 'data-wp-context' );
				if ( ctx ) {
					try {
						if ( JSON.parse( ctx ).tab === tab ) b.classList.add( 'is-active' );
					} catch ( e ) {}
				}
			} );
		},
		incrementQty() {
			state.qty += 1;
			snapPackToQty( state );
		},
		decrementQty() {
			if ( state.qty > 1 ) {
				state.qty -= 1;
				snapPackToQty( state );
			}
		},
		setQty( event ) {
			let v = parseInt( event.target.value, 10 );
			if ( isNaN( v ) || v < 1 ) v = 1;
			state.qty = v;
			snapPackToQty( state );
		},
		*addToCart() {
			const flavor = state.activeFlavor;
			if ( ! flavor ) return;
			// Use the classic WC AJAX endpoint (form-encoded) so the
			// PackPricing service's `addPackCartItemData` hook — which
			// reads $_POST['pack_price' | 'pack_label' | 'pack_quantity']
			// — receives the pack data. Store API JSON bodies don't
			// populate $_POST so pack pricing would silently drop there.
			// Send the cumulative step total + active tier for the current
			// quantity. The server (PackPricing) recomputes the authoritative
			// price from product meta; pack_price presence is what engages the
			// pack-pricing hook, so it must be sent for any quantity.
			const tier = packActiveTier( flavor, state.qty );
			const total = computePackTotal( flavor, state.qty );
			const hasPacks = flavor.packOptions?.length;
			const body = new URLSearchParams();
			body.append( 'product_id', String( flavor.id ) );
			body.append( 'quantity', String( state.qty ) );
			if ( hasPacks ) {
				body.append( 'pack_price', String( total ) );
				body.append( 'pack_label', tier ? tier.label : '' );
				body.append( 'pack_quantity', String( state.qty ) );
			}
			const wcAjaxUrl = ( window.woocommerce_params?.wc_ajax_url )
				? window.woocommerce_params.wc_ajax_url.replace( '%%endpoint%%', 'add_to_cart' )
				: '/?wc-ajax=add_to_cart';
			try {
				const response = yield fetch( wcAjaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: body.toString(),
				} );
				if ( response.ok ) {
					document.body.dispatchEvent( new Event( 'wc_fragment_refresh' ) );
					document.dispatchEvent( new Event( 'wc-blocks_added_to_cart' ) );
				}
			} catch ( err ) { /* surfaced via Phase 4 verification */ }
		},
	},
	callbacks: {
		applyFlavorVars: () => {
			const f = state.activeFlavor;
			if ( ! f ) return;
			const root = document.querySelector( '.yb-single-product' );
			if ( ! root ) return;
			if ( f.pageBg ) root.style.setProperty( '--page-bg', f.pageBg );
			if ( f.pageContrast ) root.style.setProperty( '--page-contrast', f.pageContrast );
			if ( f.nameColor ) root.style.setProperty( '--name-color', f.nameColor );
			root.querySelectorAll( '.yb-single-product__flavorCard' ).forEach( ( card ) => {
				card.classList.remove( 'is-active' );
				const ctx = card.getAttribute( 'data-wp-context' );
				if ( ctx ) {
					try {
						if ( JSON.parse( ctx ).id === state.activeId ) {
							card.classList.add( 'is-active' );
						}
					} catch ( e ) {}
				}
			} );
		},
	},
} );

// Sticky buy card — pins to viewport top with `position: fixed` once the
// user scrolls past its initial position. Required because sticky CSS
// can't span past the block (the block is one child of <main> and `<main>`
// continues with related products/etc. below). A wrapper preserves the
// flex slot so the layout doesn't jump when the card pops out of flow.
( function setupStickyBuyCard() {
	const root = document.querySelector( '[data-wp-interactive="delta9/singleProduct"]' );
	if ( ! root ) return;
	const buyCard = root.querySelector( '.yb-single-product__buyCard' );
	if ( ! buyCard || ! buyCard.parentNode ) return;

	const wrap = document.createElement( 'div' );
	wrap.className = 'yb-single-product__buyCardWrap';
	buyCard.parentNode.insertBefore( wrap, buyCard );
	wrap.appendChild( buyCard );

	// Breathing room between whatever the card pins under and the card.
	const STICKY_GAP = 40;
	// The theme header is `position: sticky`, so the pinned card has to clear
	// its height or it slides underneath. Measured on each pass rather than
	// hardcoded, so a taller/shorter header per breakpoint stays correct.
	const siteHeader = document.querySelector( 'header.wp-block-template-part' );

	function stickyTop() {
		if ( ! siteHeader ) return STICKY_GAP;
		const position = window.getComputedStyle( siteHeader ).position;
		const pinsToViewport = position === 'sticky' ? true : position === 'fixed';
		if ( ! pinsToViewport ) return STICKY_GAP;
		return siteHeader.getBoundingClientRect().height + STICKY_GAP;
	}

	let cardW = 0;
	let cardH = 0;
	// The pinned card stops at the bottom of the product's content. That is
	// the post-content wrapper: this block now lives in each product's
	// content (so its awards are per-product), and the template renders that
	// content with core/post-content rather than the WooCommerce
	// product-details block this used to measure.
	const detailsBlock = document.querySelector( '.wp-block-post-content' );

	function measure() {
		// Briefly unpin to read the card's natural dimensions, then
		// stamp them onto the wrap so its slot survives the pin.
		const wasPinned = buyCard.classList.contains( 'is-pinned' );
		if ( wasPinned ) {
			buyCard.classList.remove( 'is-pinned' );
			buyCard.style.top = '';
			buyCard.style.right = '';
			buyCard.style.width = '';
		}
		cardW = buyCard.offsetWidth;
		cardH = buyCard.offsetHeight;
		wrap.style.width = cardW + 'px';
		wrap.style.height = cardH + 'px';
	}

	function apply() {
		const wrapRect = wrap.getBoundingClientRect();
		// The card rides the top of the viewport until its bottom would pass
		// the end of the product-details block. From there it parks on that
		// edge and scrolls off with the page instead of being released back
		// to its slot, which reads as the card vanishing.
		const stickTop = stickyTop();
		const detailsRect = detailsBlock ? detailsBlock.getBoundingClientRect() : null;
		const limitTop = detailsRect ? detailsRect.bottom - cardH : stickTop;
		if ( wrapRect.top < stickTop ) {
			const rightOffset = Math.max( 0, window.innerWidth - wrapRect.right );
			buyCard.classList.add( 'is-pinned' );
			buyCard.style.top = Math.min( stickTop, limitTop ) + 'px';
			buyCard.style.right = rightOffset + 'px';
			buyCard.style.width = cardW + 'px';
		} else {
			buyCard.classList.remove( 'is-pinned' );
			buyCard.style.top = '';
			buyCard.style.right = '';
			buyCard.style.width = '';
		}
	}

	let ticking = false;
	function onScroll() {
		if ( ! ticking ) {
			requestAnimationFrame( () => {
				apply();
				ticking = false;
			} );
			ticking = true;
		}
	}

	function onResize() {
		measure();
		apply();
	}

	measure();
	apply();
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onResize );
} )();
</script>
