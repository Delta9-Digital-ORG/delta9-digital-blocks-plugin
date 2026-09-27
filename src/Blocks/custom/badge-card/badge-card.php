<?php

/**
 * Template for the Badge Card Block (child of Badge Cards).
 *
 * The footer shows the link when a link label is set, otherwise the step/when labels,
 * so the card renders the same whichever footer variant the grid uses.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$badge = Helpers::checkAttr('badgeCardBadge', $attributes, $manifest);
$accent = \sanitize_key((string) Helpers::checkAttr('badgeCardAccent', $attributes, $manifest));
$title = Helpers::checkAttr('badgeCardTitle', $attributes, $manifest);
$body = Helpers::checkAttr('badgeCardBody', $attributes, $manifest);
$linkLabel = Helpers::checkAttr('badgeCardLinkLabel', $attributes, $manifest);
$linkUrl = Helpers::checkAttr('badgeCardLinkUrl', $attributes, $manifest);
$stepLabel = Helpers::checkAttr('badgeCardStepLabel', $attributes, $manifest);
$whenLabel = Helpers::checkAttr('badgeCardWhenLabel', $attributes, $manifest);

$cardClass = Helpers::classnames([
	$blockClass,
	'd9-bcard',
	$linkLabel && $linkUrl ? 'd9-bcard--has-link' : '',
]);
$cardStyle = $accent ? "--d9-bcard-accent:var(--wp--preset--color--{$accent});" : '';

?>
<article class="<?php echo esc_attr($cardClass); ?>"<?php echo $cardStyle ? ' style="' . esc_attr($cardStyle) . '"' : ''; ?>>
	<?php if ($badge) { ?>
		<span class="d9-bcard__badge"><?php echo esc_html(wp_strip_all_tags($badge)); ?></span>
	<?php } ?>
	<div class="d9-bcard__body">
		<span class="d9-bcard__cut" aria-hidden="true"></span>
		<span class="d9-bcard__fx" aria-hidden="true"></span>
		<span class="d9-bcard__fy" aria-hidden="true"></span>
		<?php if ($title) { ?>
			<h3 class="d9-bcard__title"><?php echo wp_kses_post($title); ?></h3>
		<?php } ?>
		<?php if ($body) { ?>
			<p class="d9-bcard__text"><?php echo wp_kses_post($body); ?></p>
		<?php } ?>
		<?php if ($linkLabel || $stepLabel || $whenLabel) { ?>
			<footer class="d9-bcard__foot">
				<?php if ($linkLabel && $linkUrl) { ?>
					<a class="d9-bcard__link" href="<?php echo esc_url($linkUrl); ?>"><?php echo esc_html(wp_strip_all_tags($linkLabel)); ?></a>
				<?php } else { ?>
					<?php if ($stepLabel) { ?>
						<span class="d9-bcard__step"><?php echo esc_html(wp_strip_all_tags($stepLabel)); ?></span>
					<?php } ?>
					<?php if ($whenLabel) { ?>
						<span class="d9-bcard__when"><?php echo esc_html(wp_strip_all_tags($whenLabel)); ?></span>
					<?php } ?>
				<?php } ?>
			</footer>
		<?php } ?>
	</div>
</article>
