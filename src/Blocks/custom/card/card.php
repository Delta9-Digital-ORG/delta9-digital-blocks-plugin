<?php

/**
 * Template for the Card Block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render('card', Helpers::props('card', $attributes));
