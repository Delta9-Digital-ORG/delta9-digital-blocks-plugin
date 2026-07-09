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
 * Resolve the product's top-level product_cat. The taxonomy has a wrapper
 * term `product-categories` sitting above the real top-level buckets
 * (`beverages`, `gummies`, `chocolate-bars`), so we walk ancestors and pick
 * the term whose direct parent is that wrapper (or the term itself if it
 * has no parent at all).
 */
$top_cat       = null;
$wrapper_slug  = 'product-categories';
$wrapper       = get_term_by( 'slug', $wrapper_slug, 'product_cat' );
$wrapper_id    = ( $wrapper && ! is_wp_error( $wrapper ) ) ? (int) $wrapper->term_id : 0;

$term_ids = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'ids' ] );
if ( ! is_wp_error( $term_ids ) ) {
	foreach ( $term_ids as $term_id ) {
		$current = get_term( (int) $term_id, 'product_cat' );
		while ( $current && ! is_wp_error( $current ) ) {
			// Match only the direct child of the wrapper. Without the wrapper
			// fall back to a true top-level term (parent === 0).
			if ( $wrapper_id ? (int) $current->parent === $wrapper_id : 0 === (int) $current->parent ) {
				$top_cat = $current;
				break 2;
			}
			if ( 0 === (int) $current->parent ) {
				// Hit a top-level that isn't under the wrapper — skip and try
				// the next assigned term.
				break;
			}
			$current = get_term( (int) $current->parent, 'product_cat' );
		}
	}
}

if ( ! $top_cat ) {
	return;
}

/**
 * Optional explicit sibling override from the block attribute. Empty array
 * (default) → use category-based discovery.
 *
 * @var int[] $explicit_ids
 */
$explicit_ids = $attributes['singleProductExplicitIds'] ?? [];

$query_args = [
	'limit'   => -1,
	'status'  => 'publish',
	'orderby' => 'menu_order',
	'order'   => 'ASC',
];

if ( ! empty( $explicit_ids ) ) {
	$query_args['include'] = array_map( 'intval', $explicit_ids );
} else {
	$query_args['category'] = [ $top_cat->slug ];
}

$siblings = wc_get_products( $query_args );

if ( empty( $siblings ) ) {
	$siblings = [ $product ];
}

/**
 * Build the flavor array. Keys mirror the fixture shape from
 * _mockups/single-product/app.js, so the view JS bindings don't change.
 *
 * @param \WC_Product $p
 * @return array<string,mixed>
 */
/**
 * Resolve the top-level Mood term name for a product.
 *
 * Walks the product's product_cat assignments and returns the name of
 * the first term that is a direct child of the `mood` wrapper term
 * (Anytime / Daytime / Nighttime). Walks up the parent chain so deeper
 * sub-terms still resolve to their top-level mood.
 *
 * @param int $product_id Product post ID.
 * @return string Mood label, or empty string if none.
 */
$resolve_mood = static function ( int $product_id ): string {
	$wrapper = get_term_by( 'slug', 'mood', 'product_cat' );
	if ( ! $wrapper || is_wp_error( $wrapper ) ) {
		return '';
	}
	$wrapper_id = (int) $wrapper->term_id;

	$term_ids = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );
	if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
		return '';
	}

	foreach ( $term_ids as $term_id ) {
		$current = get_term( (int) $term_id, 'product_cat' );
		while ( $current && ! is_wp_error( $current ) ) {
			if ( (int) $current->parent === $wrapper_id ) {
				return (string) $current->name;
			}
			if ( 0 === (int) $current->parent ) {
				break;
			}
			$current = get_term( (int) $current->parent, 'product_cat' );
		}
	}

	return '';
};

$build_flavor = static function ( \WC_Product $p ) use ( $resolve_mood ): array {
	$id = $p->get_id();

	// Pack options drive the size picker. Same meta key + shape as the
	// dropdown-picker block (`_dropdown_pack_options`) so PackPricing
	// server-side hooks (priceHtml lookups, cart price overrides) work
	// without changes.
	$raw_pack = get_post_meta( $id, '_dropdown_pack_options', true );
	$packs_in = $raw_pack ? json_decode( $raw_pack, true ) : [];
	$pack_options = [];
	if ( is_array( $packs_in ) ) {
		foreach ( $packs_in as $opt ) {
			$price = (float) ( $opt['price'] ?? 0 );
			$pack_options[] = [
				'label'     => (string) ( $opt['label'] ?? '' ),
				'quantity'  => (int) ( $opt['quantity'] ?? 1 ),
				'price'     => $price,
				'priceHtml' => html_entity_decode( wp_strip_all_tags( wc_price( $price ) ), ENT_QUOTES, 'UTF-8' ),
			];
		}
	}

	// Short label override for the flavor-card chip text. When set,
	// only the small card label uses this; the buy-card h2, wordmark,
	// and image alt keep using the full product name.
	$card_label = (string) get_post_meta( $id, '_yb_card_label', true );

	// Full WC gallery (featured + gallery images) — feeds the
	// product-image-gallery-slot block via iAPI state so external slot
	// blocks swap their image when the flavor picker changes the active
	// product.
	$gallery_ids = array_filter( array_merge(
		[ (int) $p->get_image_id() ],
		array_map( 'intval', (array) $p->get_gallery_image_ids() )
	) );
	$gallery = array_values( array_filter( array_map( static function ( $aid ) use ( $p ) {
		$src = wp_get_attachment_image_url( (int) $aid, 'full' );
		if ( ! $src ) {
			return null;
		}
		$alt = (string) get_post_meta( (int) $aid, '_wp_attachment_image_alt', true );
		if ( '' === $alt ) {
			$alt = $p->get_name();
		}
		return [
			'id'  => (int) $aid,
			'src' => (string) $src,
			'alt' => $alt,
		];
	}, $gallery_ids ) ) );

	return [
		'id'           => $id,
		'name'         => $p->get_name(),
		'cardLabel'    => $card_label ?: $p->get_name(),
		'permalink'    => get_permalink( $id ),
		'priceHtml'    => html_entity_decode( wp_strip_all_tags( $p->get_price_html() ), ENT_QUOTES, 'UTF-8' ),
		'subtitle'     => (string) get_post_meta( $id, '_custom_product_servings_per_container_text_field', true ),
		'image'        => wp_get_attachment_image_url( $p->get_image_id(), 'large' ) ?: '',
		'cardImage'    => wp_get_attachment_image_url( $p->get_image_id(), 'medium' ) ?: '',
		'cardBg'       => (string) get_post_meta( $id, '_yb_card_bg', true ),
		'nameColor'    => (string) get_post_meta( $id, '_yb_name_color', true ),
		'pageBg'       => (string) get_post_meta( $id, '_yb_page_bg', true ),
		'pageContrast' => (string) get_post_meta( $id, '_yb_name_color', true ),
		'description'  => (string) get_post_meta( $id, '_custom_product_description_text_field', true ),
		'cannafacts'   => trim( implode( "\n", array_filter( [
			get_post_meta( $id, '_custom_product_per_serving_text_field', true ),
			get_post_meta( $id, '_custom_product_serving_size_text_field', true ),
			get_post_meta( $id, '_custom_product_servings_per_container_text_field', true ),
		] ) ) ),
		'ingredients'  => (string) get_post_meta( $id, '_custom_product_ingredients_text_field', true ),
		'starsAvg'     => (float) $p->get_average_rating(),
		'reviewCount'  => (int) $p->get_review_count(),
		'packOptions'  => $pack_options,
		'mood'         => $resolve_mood( $id ),
		'gallery'      => $gallery,
	];
};

$flavors = array_values( array_map( $build_flavor, $siblings ) );

/**
 * Pick the active flavor — current product if it's in the sibling list,
 * otherwise fall back to the first sibling.
 */
$active = null;
foreach ( $flavors as $flavor ) {
	if ( $flavor['id'] === $product->get_id() ) {
		$active = $flavor;
		break;
	}
}
if ( ! $active ) {
	$active = $flavors[0];
}

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
 * attribute of `[ [ 'url' => …, 'alt' => … ], … ]`, falls back to the
 * legacy single `singleProductBadgeUrl` / `singleProductBadgeAlt` pair,
 * and finally to the default SVG shipped with the plugin.
 */
$badge_alt = $attributes['singleProductBadgeAlt'] ?? __( 'Award badge', 'delta9-digital-blocks-plugin' );
$badge_url = $attributes['singleProductBadgeUrl'] ?? '';

$badges_attr = $attributes['singleProductBadges'] ?? [];
$badges = [];
if ( is_array( $badges_attr ) && ! empty( $badges_attr ) ) {
	foreach ( $badges_attr as $b ) {
		if ( ! is_array( $b ) ) {
			continue;
		}
		$badges[] = [
			'url' => (string) ( $b['url'] ?? '' ),
			'alt' => (string) ( $b['alt'] ?? $badge_alt ),
		];
	}
} elseif ( $badge_url ) {
	$badges[] = [ 'url' => $badge_url, 'alt' => $badge_alt ];
} else {
	// One default badge — the shipped SVG, inlined so --fill-0 can recolor it.
	$badges[] = [ 'url' => '', 'alt' => $badge_alt ];
}

$initial_style = sprintf(
	'--page-bg: %s; --page-contrast: %s; --name-color: %s;',
	esc_attr( $active['pageBg'] ?: '#fff' ),
	esc_attr( $active['pageContrast'] ?: '#117571' ),
	esc_attr( $active['nameColor'] ?: '#117571' )
);

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
					style="--flavor-bg: <?php echo esc_attr( $flavor['cardBg'] ?: '#ccc' ); ?>; --flavor-name: <?php echo esc_attr( $flavor['nameColor'] ?: '#117571' ); ?>;"
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
					data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'tab' => 'ingredients' ] ) ); ?>'
					data-wp-on--click="actions.selectTab"
				><?php esc_html_e( 'Ingredients', 'delta9-digital-blocks-plugin' ); ?></button>
			</div>

			<div class="yb-single-product__panelCard">
				<h3 data-wp-text="state.panelTitle"><?php echo esc_html( strtok( $active['description'], "\n" ) ?: '' ); ?></h3>
				<div class="yb-single-product__panelCard__body" data-wp-text="state.panelBody"><?php
					$body_start = strpos( $active['description'], "\n\n" );
					echo esc_html( false !== $body_start ? trim( substr( $active['description'], $body_start ) ) : '' );
				?></div>
			</div>
		</div>

	</div>

	<div class="yb-single-product__purchase">
		<div class="yb-single-product__purchaseTop">
			<div class="yb-single-product__awards">
				<?php foreach ( $badges as $badge ) : ?>
					<?php if ( $badge['url'] ) : ?>
						<img class="yb-single-product__award" src="<?php echo esc_url( $badge['url'] ); ?>" alt="<?php echo esc_attr( $badge['alt'] ); ?>" />
					<?php else : ?>
						<div class="yb-single-product__award" aria-label="<?php echo esc_attr( $badge['alt'] ); ?>">
							<?php
							$badge_path = __DIR__ . '/assets/awards-badge.svg';
							if ( file_exists( $badge_path ) ) {
								// SVG is authored with fill="var(--fill-0, …)" — inlined
								// so the active flavor's contrast color cascades into it.
								readfile( $badge_path ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — SVG file shipped by the plugin.
							}
							?>
						</div>
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

		<div class="yb-single-product__wordmark" data-wp-text="state.activeFlavor.name"><?php echo esc_html( $active['name'] ); ?></div>
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
	const tier = packActiveTier( flavor, qty );
	if ( ! tier ) return qty * base;
	return tier.price + ( qty - tier.quantity ) * base;
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
		get panelTitle() {
			const f = state.activeFlavor;
			if ( ! f ) return '';
			if ( state.tab === 'description' ) {
				return ( f.description || '' ).split( '\n\n' )[ 0 ] || '';
			}
			if ( state.tab === 'cannafacts' ) return 'Nutritional facts';
			if ( state.tab === 'ingredients' ) return 'Ingredients';
			return '';
		},
		get panelBody() {
			const f = state.activeFlavor;
			if ( ! f ) return '';
			if ( state.tab === 'description' ) {
				return ( f.description || '' ).split( '\n\n' ).slice( 1 ).join( '\n\n' );
			}
			if ( state.tab === 'cannafacts' ) return f.cannafacts || '';
			if ( state.tab === 'ingredients' ) return f.ingredients || '';
			return '';
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

	const STICKY_TOP = 24;
	let cardW = 0;
	let cardH = 0;

	function measure() {
		// Briefly unpin to read the card's natural dimensions, then
		// stamp them onto the wrap so its slot survives the pin.
		const wasPinned = buyCard.classList.contains( 'is-pinned' );
		if ( wasPinned ) {
			buyCard.classList.remove( 'is-pinned' );
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
		if ( wrapRect.top < STICKY_TOP ) {
			const rightOffset = Math.max( 0, window.innerWidth - wrapRect.right );
			buyCard.classList.add( 'is-pinned' );
			buyCard.style.right = rightOffset + 'px';
			buyCard.style.width = cardW + 'px';
		} else {
			buyCard.classList.remove( 'is-pinned' );
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
