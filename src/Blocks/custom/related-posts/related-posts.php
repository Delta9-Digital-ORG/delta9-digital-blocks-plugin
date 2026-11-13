<?php

/**
 * Template for the Related Posts Block (handoff design_handoff_blog_post section 6).
 *
 * Posts from the current post's primary category, topped up with the latest posts; each a
 * notched card with category (accent), date, title, excerpt and "Read →". Meant to sit in a
 * Notch Panel ("Keep reading") in the single post template.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\Insights\InsightsHelper;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$count = \max(1, \min(6, (int) Helpers::checkAttr('relatedPostsCount', $attributes, $manifest)));

$current = get_post();
$exclude = $current ? [$current->ID] : [];
$related = [];

$primary = $current ? InsightsHelper::primaryCategory($current) : null;
if ($primary) {
	$related = get_posts([
		'post_type' => 'post',
		'posts_per_page' => $count,
		'post__not_in' => $exclude,
		'cat' => $primary->term_id,
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
	]);
}

if (\count($related) < $count) {
	$related = \array_merge($related, get_posts([
		'post_type' => 'post',
		'posts_per_page' => $count - \count($related),
		'post__not_in' => \array_merge($exclude, \wp_list_pluck($related, 'ID')),
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
	]));
}

if (!$related) {
	return;
}

// Reference rotation for these cards: yellow, mint, cyan (when a category has no accent).
$cycle = ['yellow', 'mint', 'cyan'];

?>
<div class="<?php echo esc_attr(Helpers::classnames([$blockClass, 'd9-related'])); ?>">
	<?php foreach ($related as $i => $post) { ?>
		<?php
		$category = InsightsHelper::primaryCategory($post);
		$map = InsightsHelper::categoryAccents();
		$accent = $category && isset($map[$category->slug]) ? $map[$category->slug] : $cycle[$i % \count($cycle)];
		?>
		<article class="d9-related__card" style="<?php echo esc_attr('--d9-accent:var(--wp--preset--color--' . \sanitize_key($accent) . ');'); ?>">
			<span class="d9-related__cut" aria-hidden="true"></span>
			<span class="d9-related__fillet d9-related__fillet--x" aria-hidden="true"></span>
			<span class="d9-related__fillet d9-related__fillet--y" aria-hidden="true"></span>
			<p class="d9-related__meta">
				<?php if ($category) { ?>
					<span class="d9-related__category"><?php echo esc_html($category->name); ?></span>
				<?php } ?>
				<time class="d9-related__date" datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html(get_the_date('M j, Y', $post)); ?></time>
			</p>
			<h3 class="d9-related__title">
				<a class="d9-related__link" href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a>
			</h3>
			<?php if (has_excerpt($post)) { ?>
				<p class="d9-related__excerpt"><?php echo esc_html(get_the_excerpt($post)); ?></p>
			<?php } ?>
			<span class="d9-related__read" aria-hidden="true"><?php esc_html_e('Read', 'delta9-digital-blocks-plugin'); ?> →</span>
		</article>
	<?php } ?>
</div>
