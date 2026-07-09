<?php

/**
 * Template for the Product Image Gallery block.
 *
 * The block holds nested core blocks (image / cover / group / columns).
 * This PHP template wraps the resolved inner content in a
 * `wp-block-group` div so it inherits the standard Group block styling
 * (alignwide / alignfull behavior, theme.json layout, etc.) while still
 * letting admins reorder the children and transform individual images
 * to cover blocks in the editor.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest   = Helpers::getManifestByDir(__DIR__);
$blockClass = $attributes['blockClass'] ?? '';

// Eightshift's Helpers::render exposes the resolved inner-block markup
// as $renderContent. No inner content yet → nothing to render on the
// frontend (the editor placeholder handles that surface).
$inner = isset( $renderContent ) ? (string) $renderContent : '';

if ( '' === trim( $inner ) ) {
	return;
}

?>
<div class="wp-block-group <?php echo esc_attr( $blockClass ); ?> product-image-gallery">
	<?php
	// $renderContent (assigned to $inner) is the parser's rendered output
	// of nested core blocks — already escaped/sanitized by core. Echoing
	// raw is the standard pattern for blocks that wrap InnerBlocks content.
	echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</div>
