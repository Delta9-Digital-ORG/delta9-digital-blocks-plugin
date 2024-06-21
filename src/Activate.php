<?php

/**
 * The file that defines actions on plugin activation.
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Plugin\HasActivationInterface;

/**
 * The plugin activation class.
 *
 * @since 1.0.0
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
		\flush_rewrite_rules();
	}
}
