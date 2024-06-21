<?php

/**
 * Template for the Social networks block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
?>

<div class="<?php echo esc_attr($blockClass); ?>">
	<?php
		echo Components::render('social-networks', Components::props('socialNetworks', $attributes));
	?>
</div>
