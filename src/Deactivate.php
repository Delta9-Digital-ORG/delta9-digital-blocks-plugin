<?php

/**
 * The file that defines actions on plugin deactivation.
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Plugin\HasDeactivationInterface;

/**
 * The plugin deactivation class.
 */
class Deactivate implements HasDeactivationInterface
{

	/**
	 * Deactivate the plugin.
	 */
	public function deactivate(): void
	{
		\flush_rewrite_rules();
	}
}
