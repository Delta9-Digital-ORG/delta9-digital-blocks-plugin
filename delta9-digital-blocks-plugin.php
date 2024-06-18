<?php

/**
 * Plugin Name: Delta9 Digital Blocks Plugin
 * Description: This is the Gutenberg blocks plugin from Delta9 Digital.
 * Author: Delta9 Digital
 * Author URI: https://delta9digital.com/
 * Version: 1.0.0
 * License: MIT
 * License URI: http://www.gnu.org/licenses/gpl.html
 * Text Domain: delta9-digital-blocks-plugin
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin;

use Delta9DigitalBlocksPlugin\Main\Main;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Cli\Cli as Cli;

/**
 * If this file is called directly, abort.
 */
if (! \defined('WPINC')) {
	die;
}

/**
 * Include the autoloader so we can dynamically include the rest of the classes.
 */
if (!\file_exists(__DIR__ . '/vendor/autoload.php')) {
	return;
}

/**
 * Include the autoloader so we can dynamically include the rest of the classes.
 */
$loader = require __DIR__ . '/vendor/autoload.php';
//require __DIR__ . '/vendor-prefixed/autoload.php';

/**
 * The code that runs during plugin activation.
 */
\register_activation_hook(
	__FILE__,
	function () {
		PluginFactory::activate();
	}
);

/**
 * The code that runs during plugin deactivation.
 */
\register_deactivation_hook(
	__FILE__,
	function () {
		PluginFactory::deactivate();
	}
);

if (\class_exists(ManifestCache::class)) {
	(new ManifestCache())->setAllCache();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
if (\class_exists(Main::class)) {
	(new Main($loader->getPrefixesPsr4(), __NAMESPACE__))->register();
}

/**
 * Run all WPCLI commands.
 */
if (class_exists(Cli::class)) {
	(new Cli())->load('delta9digital-blocks');
}
