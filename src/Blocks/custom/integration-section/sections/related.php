<?php

/**
 * Integration section: "Other integrations" (d9_related_ids, else the same layer first, then
 * the rest in hub order; eight tiles).
 *
 * @package Delta9DigitalBlocksPlugin
 */

$limit = 8;
$relatedPosts = $data['relatedIds'] ? get_posts([
	'post_type' => 'integration',
	'post__in' => $data['relatedIds'],
	'orderby' => 'post__in',
	'posts_per_page' => $limit,
	'no_found_rows' => true,
]) : [];

if (\count($relatedPosts) < $limit) {
	$exclude = \array_merge([$data['post']->ID], \wp_list_pluck($relatedPosts, 'ID'));
	$base = [
		'post_type' => 'integration',
		'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
		'no_found_rows' => true,
	];
	if ($data['layer']) {
		$relatedPosts = \array_merge($relatedPosts, get_posts($base + [
			'posts_per_page' => $limit - \count($relatedPosts),
			'post__not_in' => $exclude,
			'tax_query' => [['taxonomy' => 'integration_layer', 'terms' => $data['layer']->term_id]], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		]));
		$exclude = \array_merge($exclude, \wp_list_pluck($relatedPosts, 'ID'));
	}
	if (\count($relatedPosts) < $limit) {
		// Hub order: layer (term order), then position within the layer.
		$rest = get_posts($base + ['posts_per_page' => 100, 'post__not_in' => $exclude]);
		$layerOrder = static function (WP_Post $item): int {
			$terms = get_the_terms($item, 'integration_layer');
			return \is_array($terms) && $terms ? (int) $terms[0]->term_id : \PHP_INT_MAX;
		};
		\usort($rest, static fn($a, $b) => [$layerOrder($a), $a->menu_order, $a->post_title] <=> [$layerOrder($b), $b->menu_order, $b->post_title]); // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		$relatedPosts = \array_merge($relatedPosts, \array_slice($rest, 0, $limit - \count($relatedPosts)));
	}
}

if (!$relatedPosts) {
	return;
}
?>
<div class="d9-int-head">
	<h2 class="d9-int-head__title d9-int-head__title--small"><?php echo esc_html($customTitle ?: __('Other integrations', 'delta9-digital-blocks-plugin')); ?></h2>
	<a class="d9-int-link" href="<?php echo esc_url($hubUrl); ?>"><?php esc_html_e('See the full hub', 'delta9-digital-blocks-plugin'); ?> <span aria-hidden="true">→</span></a>
</div>
<div class="d9-int-tiles">
	<?php foreach ($relatedPosts as $other) { ?>
		<?php
		$otherLogo = (int) get_post_meta($other->ID, 'd9_logo_id', true);
		$otherAccent = \sanitize_key((string) get_post_meta($other->ID, 'd9_accent', true)) ?: 'mint';
		$otherLabel = (string) get_post_meta($other->ID, 'd9_layer_label', true);
		if (!$otherLabel) {
			$otherTerms = get_the_terms($other, 'integration_layer');
			$otherLabel = \is_array($otherTerms) ? ($otherTerms[0]->name ?? '') : '';
		}
		?>
		<a class="d9-int-tile" href="<?php echo esc_url(get_permalink($other)); ?>" style="<?php echo esc_attr("--d9-int-tile-accent:var(--wp--preset--color--{$otherAccent});"); ?>">
			<span class="d9-int-tile__bar" aria-hidden="true"></span>
			<span class="d9-int-tile__logo">
				<?php
				if ($otherLogo) {
					echo wp_get_attachment_image($otherLogo, 'medium', false, ['alt' => get_the_title($other), 'loading' => 'lazy']);
				} else {
					echo '<span class="d9-int__logo-name">' . esc_html(get_the_title($other)) . '</span>';
				}
				?>
			</span>
			<span class="d9-int-tile__layer"><?php echo esc_html($otherLabel); ?></span>
		</a>
	<?php } ?>
</div>
