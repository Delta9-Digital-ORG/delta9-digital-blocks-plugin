<?php

/**
 * Template for the Sticky Steps Block.
 *
 * Service page offerings (design handoff round 2, blocks/sticky-steps.md): a pinned intro
 * (optional pill eyebrow, h2, lead, pill button) beside the numbered-rows component.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$anchor = $attributes['anchor'] ?? '';

$eyebrow = (string) Helpers::checkAttr('stickyStepsEyebrow', $attributes, $manifest);
$heading = (string) Helpers::checkAttr('stickyStepsHeading', $attributes, $manifest);
$lead = (string) Helpers::checkAttr('stickyStepsLead', $attributes, $manifest);
$ctaLabel = (string) Helpers::checkAttr('stickyStepsCtaLabel', $attributes, $manifest);
$ctaHref = (string) Helpers::checkAttr('stickyStepsCtaHref', $attributes, $manifest);
$ctaStyle = Helpers::checkAttr('stickyStepsCtaStyle', $attributes, $manifest) === 'outline' ? 'outline' : 'fill';
$rowsEyebrow = (string) Helpers::checkAttr('stickyStepsRowsEyebrow', $attributes, $manifest);
$ratio = (string) Helpers::checkAttr('stickyStepsColumnRatio', $attributes, $manifest);
$ratio = \in_array($ratio, ['narrow', 'wide', 'rows-wide'], true) ? $ratio : 'narrow';
$stickyTop = (int) Helpers::checkAttr('stickyStepsStickyTop', $attributes, $manifest);
$rowsSize = Helpers::checkAttr('stickyStepsRowsSize', $attributes, $manifest) === 'xl' ? 'xl' : 'lg';

if ($ctaHref !== '' && \str_starts_with($ctaHref, '/')) {
	$ctaHref = home_url($ctaHref);
}

$sectionClass = Helpers::classnames([
	$blockClass,
	$attributes['className'] ?? '',
	'd9-steps',
	"d9-steps--{$ratio}",
]);

?>
<section class="<?php echo esc_attr($sectionClass); ?>"<?php echo $anchor ? ' id="' . esc_attr($anchor) . '"' : ''; ?>>
	<div class="d9-steps__intro" style="<?php echo esc_attr("top:{$stickyTop}px;"); ?>">
		<?php if ($eyebrow) { ?>
			<p class="d9-steps__pill"><?php echo esc_html($eyebrow); ?></p>
		<?php } ?>
		<?php if ($heading) { ?>
			<h2 class="d9-steps__heading"><?php echo esc_html($heading); ?></h2>
		<?php } ?>
		<?php if ($lead) { ?>
			<p class="d9-steps__lead"><?php echo esc_html($lead); ?></p>
		<?php } ?>
		<?php if ($ctaLabel && $ctaHref) { ?>
			<a class="d9-steps__cta d9-steps__cta--<?php echo esc_attr($ctaStyle); ?>" href="<?php echo esc_url($ctaHref); ?>"><?php echo esc_html($ctaLabel); ?> <span aria-hidden="true">→</span></a>
		<?php } ?>
	</div>
	<div class="d9-steps__rows">
		<?php if ($rowsEyebrow) { ?>
			<p class="d9-steps__eyebrow"><?php echo esc_html($rowsEyebrow); ?></p>
		<?php } ?>
		<?php
		echo Helpers::render('numbered-rows', [ // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			'numberedRowsRows' => (array) Helpers::checkAttr('stickyStepsRows', $attributes, $manifest),
			'numberedRowsSize' => $rowsSize,
			'numberedRowsAccents' => (array) Helpers::checkAttr('stickyStepsAccents', $attributes, $manifest),
		]);
		?>
	</div>
</section>
