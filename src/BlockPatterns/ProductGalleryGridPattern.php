<?php

/**
 * Registers the "Product Gallery Grid" block pattern.
 *
 * Matches the Figma "Layout / 28" right-column gallery: a 2x2 grid of
 * `product-image-gallery-slot` blocks with the exact heights from the
 * design (393 / 215 / 218 / 390 px) and the top-right slot configured as
 * a cover with a 20% overlay dim.
 *
 * @package Delta9DigitalBlocksPlugin\BlockPatterns
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\BlockPatterns;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class ProductGalleryGridPattern
 */
class ProductGalleryGridPattern implements ServiceInterface
{
	public function register(): void
	{
		\add_action('init', [$this, 'registerPattern']);
		\add_action('init', [$this, 'registerCategory'], 9);
	}

	/**
	 * Register a custom pattern category so the gallery patterns group
	 * together in the inserter.
	 *
	 * @return void
	 */
	public function registerCategory(): void
	{
		if (\function_exists('register_block_pattern_category')) {
			\register_block_pattern_category(
				'delta9-product',
				[ 'label' => \__('Product', 'delta9-digital-blocks-plugin') ]
			);
		}
	}

	/**
	 * Register the gallery grid pattern.
	 *
	 * @return void
	 */
	public function registerPattern(): void
	{
		if (!\function_exists('register_block_pattern')) {
			return;
		}

		$content = <<<'HTML'
<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"32px","left":"32px"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:eightshift-boilerplate/product-image-gallery-slot {"productImageGallerySlotIndex":0,"productImageGallerySlotMinHeight":"393px","productImageGallerySlotObjectFit":"cover"} /-->

<!-- wp:eightshift-boilerplate/product-image-gallery-slot {"productImageGallerySlotIndex":2,"productImageGallerySlotMinHeight":"215px","productImageGallerySlotObjectFit":"cover"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:eightshift-boilerplate/product-image-gallery-slot {"productImageGallerySlotIndex":1,"productImageGallerySlotType":"cover","productImageGallerySlotMinHeight":"218px","productImageGallerySlotCoverDimRatio":20,"productImageGallerySlotCoverOverlayColor":"#000000"} /-->

<!-- wp:eightshift-boilerplate/product-image-gallery-slot {"productImageGallerySlotIndex":3,"productImageGallerySlotMinHeight":"390px","productImageGallerySlotObjectFit":"cover"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HTML;

		\register_block_pattern(
			'delta9-digital-blocks-plugin/product-gallery-grid',
			[
				'title'       => \__('Product Gallery Grid (2x2 — Figma Layout 28)', 'delta9-digital-blocks-plugin'),
				'description' => \__('Two columns of product-image-gallery-slot blocks matching the Figma right-column layout: 393/215 left, 218 cover + 390 right.', 'delta9-digital-blocks-plugin'),
				'categories'  => [ 'delta9-product' ],
				'keywords'    => [ 'product', 'gallery', 'grid', 'image' ],
				'content'     => $content,
			]
		);
	}
}
