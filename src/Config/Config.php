<?php

/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package Delta9DigitalBlocksPlugin\Config
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Config;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

/**
 * The project config class.
 */
class Config
{
	/**
	 * Method that returns project name.
	 *
	 * Generally used for naming assets handlers, languages, etc.
	 */
	public static function getProjectName(): string
	{
		return Helpers::getPluginName();
	}

	/**
	 * Method that returns project version.
	 *
	 * Generally used for versioning asset handlers while enqueueing them.
	 */
	public static function getProjectVersion(): string
	{
		return Helpers::getPluginVersion();
	}

	/**
	 * Method that returns project text domain.
	 *
	 * Generally used for caching and translations.
	 */
	public static function getProjectTextDomain(): string
	{
		return Helpers::getPluginTextDomain();
	}

	/**
	 * Method that returns project REST-API namespace.
	 *
	 * Used for namespacing projects REST-API routes and fields.
	 *
	 * @return string Project name.
	 */
	public static function getProjectRoutesNamespace(): string
	{
		// The text domain (plugin slug), NOT the display name — getProjectName()
		// resolves to the "Plugin Name" header ("Delta9 Digital Blocks Plugin"),
		// which put spaces in the REST namespace and 404'd every consumer that
		// (correctly) called the slug URL, e.g. bundle-picker's
		// rest_url('delta9-digital-blocks-plugin/v1/bundle/add').
		return self::getProjectTextDomain();
	}

	/**
	 * Method that returns project REST-API version.
	 *
	 * Used for versioning projects REST-API routes and fields.
	 *
	 * @return string Project route version.
	 */
	public static function getProjectRoutesVersion(): string
	{
		return 'v1';
	}
}
