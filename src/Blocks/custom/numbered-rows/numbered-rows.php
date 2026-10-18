<?php

/**
 * Template for the Numbered Rows Block: renders the numbered-rows component with the block's
 * own attributes (same keys).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render('numbered-rows', $attributes); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
