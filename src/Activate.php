<?php

/**
 * The file that defines actions on plugin activation.
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin;

use Delta9DigitalBlocksPlugin\ProductBadges\ProductBadges;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Plugin\HasActivationInterface;

/**
 * The plugin activation class.
 */
class Activate implements HasActivationInterface
{
	/**
	 * Activate the plugin.
	 *
	 * @since 1.0.0
	 */
	public function activate(): void
	{
		// Default badge terms (#1 Best Seller, New, ...). Also seeded on init
		// for deploys, which don't re-run activation.
		(new ProductBadges())->registerTaxonomy();
		ProductBadges::seedTerms();

		\flush_rewrite_rules();
	}
}
