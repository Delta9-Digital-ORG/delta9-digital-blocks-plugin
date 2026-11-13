<?php

/**
 * Template for the Out Of Stock Badge Component.
 *
 * Rendered over a product image when the product is out of stock. Not a
 * block on its own: OutOfStock injects it into woocommerce/product-image,
 * and the single-product block prints it on its flavor cards.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\OutOfStock\OutOfStock;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

?>
<span class="<?php echo esc_attr(Helpers::classnames([$componentClass, $additionalClass])); ?>">
	<?php echo esc_html(OutOfStock::getLabel()); ?>
</span>
