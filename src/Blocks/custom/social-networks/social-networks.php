<?php

/**
 * Template for the Social networks block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$blockClass = $attributes['blockClass'] ?? '';
?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
		echo Helpers::render('social-networks', Helpers::props('socialNetworks', $attributes));
	?>
</div>
