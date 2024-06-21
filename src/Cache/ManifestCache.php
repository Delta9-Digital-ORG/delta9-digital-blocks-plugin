<?php

/**
 * The file that defines a project config details like prefix, absolute path and etc.
 *
 * @package Delta9DigitalBlocksPlugin\Cache
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Cache;

use Delta9DigitalBlocksPlugin\Config\Config;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Cache\AbstractManifestCache;

/**
 * The project config class.
 */
class ManifestCache extends AbstractManifestCache
{
	/**
	 * Get cache name.
	 *
	 * @return string Cache name.
	 */
	public function getCacheName(): string
	{
		return Config::getProjectTextDomain();
	}

	/**
	 * Get cache version.
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		return Config::getProjectVersion();
	}
}
