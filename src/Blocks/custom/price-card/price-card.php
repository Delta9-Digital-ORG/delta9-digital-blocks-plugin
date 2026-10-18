<?php

/**
 * Template for the Price Card Block.
 *
 * A pricing card inside a Badge Cards grid (reference Pricing.dc.html): the Badge Card's notched
 * shell (d9-bcard: grid colours, cut, accent from position) without the badge. Packages use
 * eyebrow + price + feature list + button; retainers use price + name + description. Anything
 * left empty is skipped.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$eyebrow = (string) Helpers::checkAttr('priceCardEyebrow', $attributes, $manifest);
$price = (string) Helpers::checkAttr('priceCardPrice', $attributes, $manifest);
$suffix = (string) Helpers::checkAttr('priceCardSuffix', $attributes, $manifest);
$highlight = (bool) Helpers::checkAttr('priceCardHighlight', $attributes, $manifest);
$name = (string) Helpers::checkAttr('priceCardName', $attributes, $manifest);
$body = (string) Helpers::checkAttr('priceCardBody', $attributes, $manifest);
$features = \array_values(\array_filter(\array_map('trim', \explode("\n", (string) Helpers::checkAttr('priceCardFeatures', $attributes, $manifest)))));
$linkLabel = (string) Helpers::checkAttr('priceCardLinkLabel', $attributes, $manifest);
$linkUrl = (string) Helpers::checkAttr('priceCardLinkUrl', $attributes, $manifest);
$linkStyle = Helpers::checkAttr('priceCardLinkStyle', $attributes, $manifest) === 'fill' ? 'fill' : 'outline';
$accent = \sanitize_key((string) Helpers::checkAttr('priceCardAccent', $attributes, $manifest));

if ($linkUrl !== '' && \str_starts_with($linkUrl, '/')) {
	$linkUrl = home_url($linkUrl);
}

$cardClass = Helpers::classnames([
	$blockClass,
	'd9-bcard',
	'd9-pcard',
	$highlight ? 'd9-pcard--highlight' : '',
]);
$cardStyle = $accent ? "--d9-bcard-accent:var(--wp--preset--color--{$accent});" : '';

?>
<article class="<?php echo esc_attr($cardClass); ?>"<?php echo $cardStyle ? ' style="' . esc_attr($cardStyle) . '"' : ''; ?>>
	<div class="d9-bcard__body">
		<span class="d9-bcard__cut" aria-hidden="true"></span>
		<span class="d9-bcard__fx" aria-hidden="true"></span>
		<span class="d9-bcard__fy" aria-hidden="true"></span>
		<?php if ($eyebrow) { ?>
			<p class="d9-pcard__eyebrow"><?php echo esc_html($eyebrow); ?></p>
		<?php } ?>
		<p class="d9-pcard__price">
			<span class="d9-pcard__amount"><?php echo esc_html($price); ?></span>
			<?php if ($suffix) { ?>
				<span class="d9-pcard__suffix"><?php echo esc_html($suffix); ?></span>
			<?php } ?>
		</p>
		<?php if ($name) { ?>
			<h3 class="d9-pcard__name"><?php echo esc_html($name); ?></h3>
		<?php } ?>
		<?php if ($body) { ?>
			<p class="d9-bcard__text"><?php echo esc_html($body); ?></p>
		<?php } ?>
		<?php if ($features) { ?>
			<ul class="d9-pcard__list">
				<?php foreach ($features as $feature) { ?>
					<li><?php echo esc_html($feature); ?></li>
				<?php } ?>
			</ul>
		<?php } ?>
		<?php if ($linkLabel && $linkUrl) { ?>
			<div class="d9-pcard__foot">
				<a class="d9-pcard__btn d9-pcard__btn--<?php echo esc_attr($linkStyle); ?>" href="<?php echo esc_url($linkUrl); ?>"><?php echo esc_html($linkLabel); ?></a>
			</div>
		<?php } ?>
	</div>
</article>
