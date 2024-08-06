<?php

/**
 * Template for the Product Count Component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

?>
<div class="<?php echo esc_attr($componentClass); ?>">
	<span class="<?php echo esc_attr($componentClass); ?>-button <?php echo esc_attr($componentClass); ?>-decrease">-</span>
	<span class="<?php echo esc_attr($componentClass); ?>-quantity">1</span>
	<span class="<?php echo esc_attr($componentClass); ?>-button <?php echo esc_attr($componentClass); ?>-increase">+</span>
</div>
