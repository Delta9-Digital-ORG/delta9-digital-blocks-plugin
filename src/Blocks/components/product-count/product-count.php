<?php

/**
 * Template for the Product Count Component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\OutOfStock\OutOfStock;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

// A sold-out product has nothing to count. The stepper keeps its space so
// cards in a grid stay level, but is hidden and inert.
$isOutOfStock = get_the_ID() !== false && OutOfStock::isOutOfStock((int) get_the_ID());

?>
<div class="<?php echo esc_attr(Helpers::classnames([$componentClass, $isOutOfStock ? 'is-out-of-stock' : ''])); ?>"<?php echo $isOutOfStock ? ' aria-hidden="true" inert' : ''; ?>>
	<button class="<?php echo esc_attr($componentClass); ?>-button <?php echo esc_attr($componentClass); ?>-decrease">-</button>
	<span class="<?php echo esc_attr($componentClass); ?>-quantity">1</span>
	<button class="<?php echo esc_attr($componentClass); ?>-button <?php echo esc_attr($componentClass); ?>-increase">+</button>
</div>
