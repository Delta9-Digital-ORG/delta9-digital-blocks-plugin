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
