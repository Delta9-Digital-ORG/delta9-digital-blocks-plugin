<?php

/**
 * Template for the Stacked Cards Block.
 *
 * Branding offerings (design handoff round 2, blocks/stacked-cards.md): pinned intro with
 * accent jump links, and cards that pin under each other on scroll and release together
 * (assets/stack.js). Without JS, or below 860px, the cards are a plain column.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';
$anchor = $attributes['anchor'] ?? '';

$heading = (string) Helpers::checkAttr('stackedCardsHeading', $attributes, $manifest);
$lead = (string) Helpers::checkAttr('stackedCardsLead', $attributes, $manifest);
$accents = \array_values(\array_filter(\array_map('sanitize_key', (array) Helpers::checkAttr('stackedCardsAccents', $attributes, $manifest)))) ?: ['mint'];
$pinTop = (int) Helpers::checkAttr('stackedCardsPinTop', $attributes, $manifest);
$stackStep = (int) Helpers::checkAttr('stackedCardsStackStep', $attributes, $manifest);
$showJump = (bool) Helpers::checkAttr('stackedCardsShowJumpLinks', $attributes, $manifest);

$cards = [];
foreach (\array_values(\array_filter((array) Helpers::checkAttr('stackedCardsCards', $attributes, $manifest), 'is_array')) as $i => $card) {
	if (empty($card['title'])) {
		continue;
	}
	$href = (string) ($card['ctaHref'] ?? '');
	$cards[] = [
		'id' => \sanitize_title((string) ($card['id'] ?? '')) ?: 'offer-' . ($i + 1),
		'badge' => (string) ($card['badge'] ?? '') ?: \str_pad((string) ($i + 1), 2, '0', \STR_PAD_LEFT),
		'accent' => \sanitize_key((string) ($card['accent'] ?? '')) ?: $accents[$i % \count($accents)],
		'title' => (string) $card['title'],
		'ctaLabel' => (string) ($card['ctaLabel'] ?? ''),
		'ctaHref' => ($href !== '' && \str_starts_with($href, '/')) ? home_url($href) : $href,
		'body' => (string) ($card['body'] ?? ''),
		'tags' => \array_values(\array_filter(\array_map('trim', \is_array($card['tags'] ?? null) ? $card['tags'] : \explode(',', (string) ($card['tags'] ?? ''))))),
	];
}

$sectionClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	$attributes['className'] ?? '',
	'd9-stack',
]);

$accentVar = static fn(string $accent): string => "--accent:var(--wp--preset--color--{$accent});";

?>
<section class="<?php echo esc_attr($sectionClass); ?>" data-pin-top="<?php echo esc_attr((string) $pinTop); ?>" data-stack-step="<?php echo esc_attr((string) $stackStep); ?>"<?php echo $anchor ? ' id="' . esc_attr($anchor) . '"' : ''; ?>>
	<div class="d9-stack__intro">
		<?php if ($heading) { ?>
			<h2 class="d9-stack__heading"><?php echo esc_html($heading); ?></h2>
		<?php } ?>
		<?php if ($lead) { ?>
			<p class="d9-stack__lead"><?php echo esc_html($lead); ?></p>
		<?php } ?>
		<?php if ($showJump && \count($cards) > 1) { ?>
			<nav class="d9-stack__jump" aria-label="<?php echo esc_attr($heading ?: __('Offerings', 'delta9-digital-blocks-plugin')); ?>">
				<?php foreach ($cards as $card) { ?>
					<a href="#<?php echo esc_attr($card['id']); ?>" style="<?php echo esc_attr($accentVar($card['accent'])); ?>" aria-label="<?php echo esc_attr($card['title']); ?>"><?php echo esc_html($card['badge']); ?></a>
				<?php } ?>
			</nav>
		<?php } ?>
	</div>
	<div class="d9-stack__cards">
		<?php foreach ($cards as $card) { ?>
			<article id="<?php echo esc_attr($card['id']); ?>" class="d9-stack__card" data-stack-card style="<?php echo esc_attr($accentVar($card['accent'])); ?>">
				<header class="d9-stack__row">
					<div class="d9-stack__title">
						<span class="d9-stack__badge" aria-hidden="true"><?php echo esc_html($card['badge']); ?></span>
						<h3><?php echo esc_html($card['title']); ?></h3>
					</div>
					<?php if ($card['ctaLabel'] && $card['ctaHref']) { ?>
						<a class="d9-stack__cta" href="<?php echo esc_url($card['ctaHref']); ?>"><?php echo esc_html($card['ctaLabel']); ?> <span aria-hidden="true">→</span></a>
					<?php } ?>
				</header>
				<?php if ($card['body']) { ?>
					<p class="d9-stack__body"><?php echo esc_html($card['body']); ?></p>
				<?php } ?>
				<?php if ($card['tags']) { ?>
					<ul class="d9-stack__tags">
						<?php foreach ($card['tags'] as $tag) { ?>
							<li><?php echo esc_html($tag); ?></li>
						<?php } ?>
					</ul>
				<?php } ?>
			</article>
		<?php } ?>
	</div>
</section>
