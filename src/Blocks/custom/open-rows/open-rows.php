<?php

/**
 * Template for the Open Rows Block (named pattern "Open Rows"; handoff
 * design_handoff_blog_post section 5). Rows are Open Row child blocks.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$label = Helpers::checkAttr('openRowsLabel', $attributes, $manifest);

?>
<div class="<?php echo esc_attr(Helpers::classnames([$blockClass, 'd9-open-rows'])); ?>">
	<span class="d9-open-rows__cut" aria-hidden="true"></span>
	<span class="d9-open-rows__fillet d9-open-rows__fillet--x" aria-hidden="true"></span>
	<span class="d9-open-rows__fillet d9-open-rows__fillet--y" aria-hidden="true"></span>
	<?php if ($label) { ?>
		<span class="d9-open-rows__pill"><?php echo esc_html($label); ?></span>
	<?php } ?>
	<dl class="d9-open-rows__list">
		<?php
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo $renderContent;
		?>
	</dl>
</div>
