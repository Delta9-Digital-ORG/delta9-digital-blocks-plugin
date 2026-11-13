<?php

/**
 * Template for the Feature Rows Block.
 *
 * One service offering track (design handoff round 2, blocks/feature-rows.md): a stretched
 * image beside a pill eyebrow, h2 and three numbered rows, all in the track's accent. Without an
 * image the media column stays as a surface panel (the design's placeholder).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$anchor = $attributes['anchor'] ?? '';

$imageId = (int) Helpers::checkAttr('featureRowsImageId', $attributes, $manifest);
$imageUrl = (string) Helpers::checkAttr('featureRowsImageUrl', $attributes, $manifest);
$imageAlt = (string) Helpers::checkAttr('featureRowsImageAlt', $attributes, $manifest);
$side = Helpers::checkAttr('featureRowsImageSide', $attributes, $manifest) === 'right' ? 'right' : 'left';
$accent = \sanitize_key((string) Helpers::checkAttr('featureRowsAccent', $attributes, $manifest)) ?: 'mint';
$eyebrow = (string) Helpers::checkAttr('featureRowsEyebrow', $attributes, $manifest);
$heading = (string) Helpers::checkAttr('featureRowsHeading', $attributes, $manifest);

$sectionClass = Helpers::classnames([
	$blockClass,
	$attributes['className'] ?? '',
	'd9-track',
	"d9-track--img-{$side}",
]);

?>
<section class="<?php echo esc_attr($sectionClass); ?>" style="<?php echo esc_attr("--accent:var(--wp--preset--color--{$accent});"); ?>"<?php echo $anchor ? ' id="' . esc_attr($anchor) . '"' : ''; ?>>
	<figure class="d9-track__media">
		<?php
		if ($imageId) {
			echo wp_get_attachment_image($imageId, 'large', false, [
				'alt' => $imageAlt ?: (string) get_post_meta($imageId, '_wp_attachment_image_alt', true),
				'loading' => 'lazy',
				'sizes' => '(max-width: 860px) 100vw, 620px',
			]);
		} elseif ($imageUrl) {
			echo '<img src="' . esc_url($imageUrl) . '" alt="' . esc_attr($imageAlt) . '" loading="lazy" />';
		}
		?>
	</figure>
	<div class="d9-track__copy">
		<?php if ($eyebrow) { ?>
			<p class="d9-track__eyebrow"><?php echo esc_html($eyebrow); ?></p>
		<?php } ?>
		<?php if ($heading) { ?>
			<h2 class="d9-track__heading"><?php echo esc_html($heading); ?></h2>
		<?php } ?>
		<?php
		echo Helpers::render('numbered-rows', [ // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			'numberedRowsRows' => (array) Helpers::checkAttr('featureRowsRows', $attributes, $manifest),
			'numberedRowsSize' => 'md',
			'numberedRowsAccent' => $accent,
		]);
		?>
	</div>
</section>
