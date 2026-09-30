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

$accentCycle = ['mint', 'coral', 'cyan', 'yellow'];

/**
 * Render one card.
 *
 * @param WP_Post $post Post.
 * @param bool $isFeatured Big card.
 * @param int $index Position, for the accent fallback.
 */
$renderCard = static function (WP_Post $post, bool $isFeatured, int $index) use ($accentByCategory, $accentCycle, $featuredLabel): void {
	$permalink = get_permalink($post);
	$title = get_the_title($post);

	$categories = get_the_category($post->ID);
	$primary = $categories[0] ?? null;
	$accent = $primary ? ($accentByCategory[$primary->slug] ?? $accentCycle[$index % \count($accentCycle)]) : 'mint';

	$tags = get_the_tags($post->ID);
	$tags = \is_array($tags) ? \array_slice($tags, 0, 3) : [];

	$authorId = (int) $post->post_author;
	$authorName = get_the_author_meta('display_name', $authorId);
	$authorRole = (string) get_user_meta($authorId, 'd9_author_role', true);
	$avatarId = (int) get_user_meta($authorId, 'd9_avatar_id', true);
	$initials = \implode('', \array_map(
		static fn($part) => \mb_strtoupper(\mb_substr($part, 0, 1)),
		\array_slice(\preg_split('/\s+/', \trim((string) $authorName)) ?: [], 0, 2)
	));

	$date = get_the_date('M j, Y', $post);
	$readMinutes = \max(1, (int) \ceil(\str_word_count(wp_strip_all_tags((string) $post->post_content)) / 200));

	$cardClass = Helpers::classnames([
		'd9-insight',
		$isFeatured ? 'd9-insight--featured' : 'd9-insight--small',
	]);
	?>
	<article class="<?php echo esc_attr($cardClass); ?>" style="<?php echo esc_attr('--d9-accent:var(--wp--preset--color--' . \sanitize_key($accent) . ');'); ?>">
		<div class="d9-insight__inner">
			<?php if (has_post_thumbnail($post)) { ?>
				<span class="d9-insight__bg" aria-hidden="true">
					<?php
					echo get_the_post_thumbnail($post, 'large', [
						'alt' => '',
						'loading' => 'lazy',
						'sizes' => $isFeatured ? '(max-width: 860px) 100vw, 980px' : '(max-width: 860px) 100vw, 490px',
					]);
					?>
				</span>
			<?php } ?>
			<span class="d9-insight__shade" aria-hidden="true"></span>

			<?php if ($authorName) { ?>
				<span class="d9-insight__author">
					<span class="d9-insight__avatar<?php echo $avatarId ? '' : ' d9-insight__avatar--initials'; ?>">
						<?php
						if ($avatarId) {
							echo wp_get_attachment_image($avatarId, 'thumbnail', false, ['alt' => '', 'loading' => 'lazy']);
						} else {
							echo esc_html($initials);
						}
						?>
					</span>
					<span class="d9-insight__who">
						<span class="d9-insight__name"><?php echo esc_html($authorName); ?></span>
						<?php if ($authorRole) { ?>
							<span class="d9-insight__role"><?php echo esc_html($authorRole); ?></span>
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

			<<?php echo $isFeatured ? 'h2' : 'h3'; ?> class="d9-insight__title">
				<a class="d9-insight__link" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
			</<?php echo $isFeatured ? 'h2' : 'h3'; ?>>

			<?php if ($isFeatured && has_excerpt($post)) { ?>
				<p class="d9-insight__excerpt"><?php echo esc_html(get_the_excerpt($post)); ?></p>
			<?php } ?>

			<p class="d9-insight__date">
				<time datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html($date); ?></time>
				<?php if ($isFeatured) { ?>
					<?php
					/* translators: %d: minutes. */
					echo esc_html(' / ' . \sprintf(_n('%d min read', '%d min read', $readMinutes, 'delta9-digital-blocks-plugin'), $readMinutes));
					?>
				<?php } ?>
			</p>

			<?php if ($tags) { ?>
				<span class="d9-insight__tags">
					<?php foreach ($tags as $tag) { ?>
						<span class="d9-insight__tag"><?php echo esc_html($tag->name); ?></span>
					<?php } ?>
				</span>
			<?php } ?>
		</div>

		<span class="d9-insight__pill"><?php echo esc_html($isFeatured ? $featuredLabel : ($primary->name ?? '')); ?></span>
		<span class="d9-insight__read" aria-hidden="true"><?php esc_html_e('Read', 'delta9-digital-blocks-plugin'); ?> <span>→</span></span>
	</article>
	<?php
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
