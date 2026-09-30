<?php

/**
 * Template for the Insight Cards Block.
 *
 * Spec: theme claude_design_handoff/design_handoff_wp/blocks/insight-cards.md ("cards"
 * variant). One featured post (attribute, else sticky, else newest) plus two more (latest,
 * optionally in one category, or picked).
 *
 * Author chip: WP user display name, role from user meta `d9_author_role`, avatar from user
 * meta `d9_avatar_id` (attachment) or initials. No Gravatar requests.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\Insights\InsightsHelper;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$query = Helpers::checkAttr('insightCardsQuery', $attributes, $manifest);
$featuredId = (int) Helpers::checkAttr('insightCardsFeaturedId', $attributes, $manifest);
$ids = \array_values(\array_filter(\array_map('intval', (array) Helpers::checkAttr('insightCardsIds', $attributes, $manifest))));
$category = (int) Helpers::checkAttr('insightCardsCategory', $attributes, $manifest);
$featuredLabel = Helpers::checkAttr('insightCardsFeaturedLabel', $attributes, $manifest);
$accentByCategory = (array) Helpers::checkAttr('insightCardsAccentByCategory', $attributes, $manifest);
$isEditor = Helpers::checkAttr('insightCardsServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? '';
$anchor = $attributes['anchor'] ?? '';
$variant = Helpers::checkAttr('insightCardsVariant', $attributes, $manifest) === 'related' ? 'related' : 'featured';
$categorySlug = \sanitize_title((string) Helpers::checkAttr('insightCardsCategorySlug', $attributes, $manifest));

if (!$category && $categorySlug) {
	$term = get_category_by_slug($categorySlug);
	$category = $term ? (int) $term->term_id : 0;
}

// Related (service pages): three compact cards from the category, topped up with the latest.
if ($variant === 'related') {
	$base = ['post_type' => 'post', 'ignore_sticky_posts' => true, 'no_found_rows' => true];
	$related = ($query === 'manual' && $ids)
		? get_posts($base + ['post__in' => \array_slice($ids, 0, 3), 'orderby' => 'post__in', 'posts_per_page' => 3])
		: ($category ? get_posts($base + ['posts_per_page' => 3, 'cat' => $category]) : []);
	if (\count($related) < 3) {
		$related = \array_merge($related, get_posts($base + [
			'posts_per_page' => 3 - \count($related),
			'post__not_in' => \wp_list_pluck($related, 'ID') ?: [0],
		]));
	}

	if (!$related) {
		if ($isEditor) {
			echo '<p>' . esc_html__('No published posts to show yet.', 'delta9-digital-blocks-plugin') . '</p>';
		}
		return;
	}

	$relatedCycle = ['mint', 'coral', 'cyan', 'yellow'];
	$sectionClass = Helpers::classnames([$blockClass, 'd9-insights', 'd9-insights--related', $align ? "align{$align}" : '']);
	?>
	<section class="<?php echo esc_attr($sectionClass); ?>"<?php echo $anchor ? ' id="' . esc_attr($anchor) . '"' : ''; ?>>
		<div class="d9-insights__related">
			<?php foreach ($related as $i => $post) { ?>
				<?php
				$categories = get_the_category($post->ID);
				$minutes = \max(1, (int) \ceil(\str_word_count(wp_strip_all_tags((string) $post->post_content)) / 200));
				$accent = $relatedCycle[$i % \count($relatedCycle)];
				?>
				<article class="d9-rcard" style="<?php echo esc_attr("--d9-accent:var(--wp--preset--color--{$accent});"); ?>">
					<?php if ($categories) { ?>
						<p class="d9-rcard__cat"><?php echo esc_html($categories[0]->name); ?></p>
					<?php } ?>
					<h3 class="d9-rcard__title"><a class="d9-rcard__link" href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h3>
					<p class="d9-rcard__read">
						<?php
						/* translators: %d: minutes. */
						echo esc_html(\sprintf(_n('%d min read', '%d min read', $minutes, 'delta9-digital-blocks-plugin'), $minutes));
						?>
					</p>
				</article>
			<?php } ?>
		</div>
	</section>
	<?php
	return;
}

// Featured post.
$featured = $featuredId ? get_post($featuredId) : null;
if (!$featured || $featured->post_status !== 'publish' || $featured->post_type !== 'post') {
	$sticky = \array_map('intval', (array) get_option('sticky_posts', []));
	$candidates = get_posts([
		'post_type' => 'post',
		'posts_per_page' => 1,
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
		'post__in' => $sticky ?: [0],
		'cat' => $category ?: '',
	]);
	if (!$candidates) {
		$candidates = get_posts([
			'post_type' => 'post',
			'posts_per_page' => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows' => true,
			'cat' => $category ?: '',
		]);
	}
	$featured = $candidates[0] ?? null;
}

// Two more posts.
if ($query === 'manual' && $ids) {
	$others = get_posts([
		'post_type' => 'post',
		'post__in' => \array_slice($ids, 0, 2),
		'orderby' => 'post__in',
		'posts_per_page' => 2,
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
	]);
} else {
	$others = get_posts([
		'post_type' => 'post',
		'posts_per_page' => 2,
		'post__not_in' => $featured ? [$featured->ID] : [],
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
		'cat' => $category ?: '',
	]);
}

if (!$featured && !$others) {
	if ($isEditor) {
		echo '<p>' . esc_html__('No published posts to show yet.', 'delta9-digital-blocks-plugin') . '</p>';
	}
	return;
}

/**
 * Render one card.
 *
 * @param WP_Post $post Post.
 * @param bool $isFeatured Big card.
 * @param int $index Position, for the accent fallback.
 */
$renderCard = static function (WP_Post $post, bool $isFeatured, int $index) use ($accentByCategory, $featuredLabel): void {
	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo Helpers::render('insight-card', [
		'insightCardPostId' => $post->ID,
		'insightCardSize' => $isFeatured ? 'featured' : 'small',
		'insightCardAccent' => InsightsHelper::accent($post, $index, $accentByCategory),
		'insightCardPillLabel' => $isFeatured ? $featuredLabel : '',
	]);
};

$sectionClass = Helpers::classnames([
	$blockClass,
	'd9-insights',
	$align ? "align{$align}" : '',
]);

?>
<section
	class="<?php echo esc_attr($sectionClass); ?>"
	<?php echo $anchor ? 'id="' . esc_attr($anchor) . '"' : ''; ?>
>
	<div class="d9-insights__grid">
		<?php
		if ($featured) {
			$renderCard($featured, true, 0);
		}
		?>
		<?php if ($others) { ?>
			<div class="d9-insights__stack">
				<?php
				foreach ($others as $i => $other) {
					$renderCard($other, false, $i + 1);
				}
				?>
			</div>
		<?php } ?>
	</div>
</section>
