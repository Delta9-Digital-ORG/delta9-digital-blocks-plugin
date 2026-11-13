<?php

/**
 * Insight card component: one post as an image card with author chip, category pill in a
 * top-right notch and a Read pill in a bottom-left notch (handoff blocks/insight-cards.md).
 *
 * Sizes: `featured` (home, spans two columns, excerpt + reading time), `large` (Insights
 * index, excerpt + reading time, content from the top) and `small` (home side cards).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\Insights\InsightsHelper;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$post = get_post((int) Helpers::checkAttr('insightCardPostId', $attributes, $manifest));
if (!$post) {
	return;
}

$size = Helpers::checkAttr('insightCardSize', $attributes, $manifest);
$size = \in_array($size, ['featured', 'large', 'small'], true) ? $size : 'small';
$isDetailed = $size !== 'small';
$accent = Helpers::checkAttr('insightCardAccent', $attributes, $manifest) ?: InsightsHelper::accent($post);
$pillLabel = Helpers::checkAttr('insightCardPillLabel', $attributes, $manifest);
$headingLevel = (int) Helpers::checkAttr('insightCardHeadingLevel', $attributes, $manifest) ?: ($size === 'featured' ? 2 : 3);
$headingTag = 'h' . \max(2, \min(6, $headingLevel));

$permalink = get_permalink($post);
$title = get_the_title($post);
$primary = InsightsHelper::primaryCategory($post);
$tags = InsightsHelper::tags($post);
$author = InsightsHelper::author($post);
$date = get_the_date('M j, Y', $post);
$readMinutes = InsightsHelper::readingMinutes($post);

$cardClass = Helpers::classnames([
	'd9-insight',
	"d9-insight--{$size}",
]);
?>
<article class="<?php echo esc_attr($cardClass); ?>" style="<?php echo esc_attr('--d9-accent:var(--wp--preset--color--' . \sanitize_key((string) $accent) . ');'); ?>">
	<div class="d9-insight__inner">
		<?php if (has_post_thumbnail($post)) { ?>
			<span class="d9-insight__bg" aria-hidden="true">
				<?php
				echo get_the_post_thumbnail($post, 'large', [
					'alt' => '',
					'loading' => 'lazy',
					'sizes' => $size === 'small' ? '(max-width: 860px) 100vw, 490px' : '(max-width: 860px) 100vw, 980px',
				]);
				?>
			</span>
		<?php } ?>
		<span class="d9-insight__shade" aria-hidden="true"></span>

		<?php if ($author['name']) { ?>
			<span class="d9-insight__author">
				<span class="d9-insight__avatar<?php echo $author['avatarId'] ? '' : ' d9-insight__avatar--initials'; ?>">
					<?php
					if ($author['avatarId']) {
						echo wp_get_attachment_image($author['avatarId'], 'thumbnail', false, ['alt' => '', 'loading' => 'lazy']);
					} else {
						echo esc_html($author['initials']);
					}
					?>
				</span>
				<span class="d9-insight__who">
					<span class="d9-insight__name"><?php echo esc_html($author['name']); ?></span>
					<?php if ($author['role']) { ?>
						<span class="d9-insight__role"><?php echo esc_html($author['role']); ?></span>
					<?php } ?>
				</span>
			</span>
		<?php } ?>

		<span class="d9-insight__cut d9-insight__cut--top" aria-hidden="true"></span>
		<span class="d9-insight__fillet d9-insight__fillet--top-x" aria-hidden="true"></span>
		<span class="d9-insight__fillet d9-insight__fillet--top-y" aria-hidden="true"></span>
		<span class="d9-insight__cut d9-insight__cut--bottom" aria-hidden="true"></span>
		<span class="d9-insight__fillet d9-insight__fillet--bottom-x" aria-hidden="true"></span>
		<span class="d9-insight__fillet d9-insight__fillet--bottom-y" aria-hidden="true"></span>

		<<?php echo esc_attr($headingTag); ?> class="d9-insight__title">
			<a class="d9-insight__link" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
		</<?php echo esc_attr($headingTag); ?>>

		<?php if ($isDetailed && has_excerpt($post)) { ?>
			<p class="d9-insight__excerpt"><?php echo esc_html(get_the_excerpt($post)); ?></p>
		<?php } ?>

		<p class="d9-insight__date">
			<time datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html($date); ?></time>
			<?php
			if ($isDetailed) {
				/* translators: %d: minutes. */
				echo esc_html(' / ' . \sprintf(_n('%d min read', '%d min read', $readMinutes, 'delta9-digital-blocks-plugin'), $readMinutes));
			}
			?>
		</p>

		<?php if ($tags) { ?>
			<span class="d9-insight__tags">
				<?php foreach ($tags as $tag) { ?>
					<span class="d9-insight__tag"><?php echo esc_html($tag->name); ?></span>
				<?php } ?>
			</span>
		<?php } ?>
	</div>

	<span class="d9-insight__pill"><?php echo esc_html($pillLabel ?: ($primary->name ?? '')); ?></span>
	<span class="d9-insight__read" aria-hidden="true"><?php esc_html_e('Read', 'delta9-digital-blocks-plugin'); ?> <span>→</span></span>
</article>
