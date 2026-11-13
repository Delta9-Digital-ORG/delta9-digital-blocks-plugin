<?php

/**
 * Template for the Insight Archive Block (Insights index, reference Insights v2.dc.html).
 *
 * Lists the main query (posts page, category, tag, author, search) as large insight cards in
 * a glass panel with a "Latest" notch pill, category filter pills and pagination. In the
 * editor preview (no archive query) it shows the latest four posts.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\Insights\InsightsHelper;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

global $wp_query;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$label = Helpers::checkAttr('insightArchiveLabel', $attributes, $manifest);
$showFilters = Helpers::checkAttr('insightArchiveShowFilters', $attributes, $manifest);
$isEditor = Helpers::checkAttr('insightArchiveServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? 'full'; // Manifest defaults don't reach $attributes when the block has no saved attributes.

$isListing = !$isEditor && $wp_query && ($wp_query->is_home() || $wp_query->is_archive() || $wp_query->is_search());
$posts = $isListing
	? \array_filter($wp_query->posts, static fn($post) => $post instanceof WP_Post)
	: get_posts(['post_type' => 'post', 'posts_per_page' => 4, 'no_found_rows' => true]);

// Filter pills: "All" (posts page) + categories that have posts.
$postsPage = (int) get_option('page_for_posts');
$allUrl = $postsPage ? get_permalink($postsPage) : home_url('/');
$currentTerm = $isListing && is_category() ? get_queried_object() : null;
$categories = $showFilters ? get_categories(['hide_empty' => true, 'exclude' => [(int) get_option('default_category')]]) : [];

$sectionClass = Helpers::classnames([
	$blockClass,
	'd9-archive',
	$align ? "align{$align}" : '',
]);

?>
<section class="<?php echo esc_attr($sectionClass); ?>">
	<div class="d9-archive__panel">
		<span class="d9-archive__glass" aria-hidden="true"></span>
		<?php if ($label) { ?>
			<span class="d9-archive__pill"><?php echo esc_html($label); ?></span>
		<?php } ?>

		<?php if ($categories) { ?>
			<nav class="d9-archive__filters" aria-label="<?php esc_attr_e('Insight categories', 'delta9-digital-blocks-plugin'); ?>">
				<a class="d9-archive__filter<?php echo $currentTerm ? '' : ' is-active'; ?>" href="<?php echo esc_url($allUrl); ?>"<?php echo $currentTerm ? '' : ' aria-current="page"'; ?>><?php esc_html_e('All', 'delta9-digital-blocks-plugin'); ?></a>
				<?php foreach ($categories as $category) { ?>
					<?php $isCurrent = $currentTerm && (int) $currentTerm->term_id === (int) $category->term_id; ?>
					<a class="d9-archive__filter<?php echo $isCurrent ? ' is-active' : ''; ?>" href="<?php echo esc_url(get_category_link($category)); ?>"<?php echo $isCurrent ? ' aria-current="page"' : ''; ?>><?php echo esc_html($category->name); ?></a>
				<?php } ?>
			</nav>
		<?php } ?>

		<?php if ($posts) { ?>
			<div class="d9-archive__grid">
				<?php
				foreach (\array_values($posts) as $i => $post) {
					// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
					echo Helpers::render('insight-card', [
						'insightCardPostId' => $post->ID,
						'insightCardSize' => 'large',
						'insightCardAccent' => InsightsHelper::accent($post, $i),
						'insightCardHeadingLevel' => 2,
					]);
				}
				?>
			</div>

			<?php
			$pagination = $isListing ? paginate_links([
				'total' => (int) $wp_query->max_num_pages,
				'current' => \max(1, (int) get_query_var('paged')),
				'prev_text' => '← ' . __('Newer', 'delta9-digital-blocks-plugin'),
				'next_text' => __('Older', 'delta9-digital-blocks-plugin') . ' →',
				'type' => 'list',
			]) : '';
			if ($pagination) {
				?>
				<nav class="d9-archive__pages" aria-label="<?php esc_attr_e('More insights', 'delta9-digital-blocks-plugin'); ?>">
					<?php echo wp_kses_post($pagination); ?>
				</nav>
			<?php } ?>
		<?php } else { ?>
			<p class="d9-archive__empty"><?php esc_html_e('Nothing here yet. New insights are on the way.', 'delta9-digital-blocks-plugin'); ?></p>
		<?php } ?>
	</div>
</section>
