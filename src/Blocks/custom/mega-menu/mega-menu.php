<?php

/**
 * Template for the Mega Menu Block.
 *
 * Renders the Services, Work and Integrations panels from the handoff reference
 * (theme claude_design_handoff/reference/index.dc.html, "d9-mega-panel"). Each panel carries
 * the URL of the navigation item it belongs to; assets/index.js moves it into that core
 * Navigation item so hover, focus and tab order work like a native submenu. Without JS the
 * panels stay hidden here and the core dropdowns are used instead.
 *
 * Work reads `work` posts (latest four + a featured one with d9_stats); Integrations reads
 * `integration` posts grouped by `integration_layer` (d9_logo_id, menu_order).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';

$isEditor = Helpers::checkAttr('megaMenuServerSideRender', $attributes, $manifest);
$solutions = (array) Helpers::checkAttr('megaMenuSolutions', $attributes, $manifest);
$audiences = (array) Helpers::checkAttr('megaMenuAudiences', $attributes, $manifest);
$promo = (array) Helpers::checkAttr('megaMenuPromo', $attributes, $manifest);
$projects = (array) Helpers::checkAttr('megaMenuProjects', $attributes, $manifest);
$featuredWorkId = (int) Helpers::checkAttr('megaMenuFeaturedWorkId', $attributes, $manifest);
$layerColumns = (array) Helpers::checkAttr('megaMenuLayerColumns', $attributes, $manifest);

$url = static fn(string $path): string => \str_starts_with($path, '/') ? home_url($path) : $path;
$triggers = [
	'services' => $url((string) Helpers::checkAttr('megaMenuServicesTrigger', $attributes, $manifest)),
	'work' => $url((string) Helpers::checkAttr('megaMenuWorkTrigger', $attributes, $manifest)),
	'integrations' => $url((string) Helpers::checkAttr('megaMenuIntegrationsTrigger', $attributes, $manifest)),
];

/**
 * A numbered link row (icon square + title + line).
 */
$row = static function (string $badge, array $item) use ($url): void {
	?>
	<a class="d9-mega__row" href="<?php echo esc_url($url((string) ($item['url'] ?? '#'))); ?>">
		<span class="d9-mega__badge" aria-hidden="true"><?php echo esc_html($badge); ?></span>
		<span class="d9-mega__row-text">
			<span class="d9-mega__row-title"><?php echo esc_html((string) ($item['title'] ?? '')); ?></span>
			<?php if (!empty($item['text'])) { ?>
				<span class="d9-mega__row-line"><?php echo esc_html((string) $item['text']); ?></span>
			<?php } ?>
		</span>
	</a>
	<?php
};

/**
 * Stat pair used by the Work and Integrations promo cards.
 */
$stat = static function (string $value, string $label): void {
	?>
	<span class="d9-mega__stat">
		<span class="d9-mega__stat-value"><?php echo esc_html($value); ?></span>
		<span class="d9-mega__stat-label"><?php echo esc_html($label); ?></span>
	</span>
	<?php
};

// Work data.
$recentWork = get_posts([
	'post_type' => 'work',
	'posts_per_page' => 5,
	'no_found_rows' => true,
	'post__not_in' => $featuredWorkId ? [$featuredWorkId] : [],
]);
$featuredWork = $featuredWorkId ? get_post($featuredWorkId) : null;
if (!$featuredWork || $featuredWork->post_status !== 'publish') {
	$withStats = get_posts([
		'post_type' => 'work',
		'posts_per_page' => 1,
		'no_found_rows' => true,
		'meta_query' => [['key' => 'd9_stats', 'compare' => 'EXISTS']], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	]);
	$featuredWork = $withStats[0] ?? null;
}
// Four recent launches, not repeating the featured case study.
$recentWork = \array_slice(\array_values(\array_filter($recentWork, static fn($post) => !$featuredWork || $post->ID !== $featuredWork->ID)), 0, 4);
$workCount = (int) (wp_count_posts('work')->publish ?? 0);

// Integrations data, grouped by layer slug.
$layers = [];
$integrationCount = 0;
foreach (\array_merge(...\array_map('array_values', $layerColumns ?: [[]])) as $layerSlug) {
	$term = get_term_by('slug', (string) $layerSlug, 'integration_layer');
	if (!$term) {
		continue;
	}
	$posts = get_posts([
		'post_type' => 'integration',
		'posts_per_page' => 50,
		'no_found_rows' => true,
		'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
		'tax_query' => [['taxonomy' => 'integration_layer', 'terms' => $term->term_id]], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	]);
	$layers[$term->slug] = [$term, $posts];
	$integrationCount += \count($posts);
}

if (!$isEditor && !$solutions && !$recentWork && !$layers) {
	return;
}

$wrapperClass = Helpers::classnames([$blockClass, $blockJsClass, 'd9-megas']);

?>
<div class="<?php echo esc_attr($wrapperClass); ?>" <?php echo $isEditor ? '' : 'hidden'; ?>>
	<?php // Services. ?>
	<div class="d9-mega" data-d9-mega-trigger="<?php echo esc_url($triggers['services']); ?>">
		<div class="d9-mega__shell">
			<div class="d9-mega__col">
				<p class="d9-mega__label"><?php esc_html_e('Our solutions', 'delta9-digital-blocks-plugin'); ?></p>
				<?php
				foreach (\array_values($solutions) as $i => $item) {
					$row(\sprintf('%02d', $i + 1), (array) $item);
				}
				?>
			</div>
			<div class="d9-mega__col">
				<p class="d9-mega__label"><?php esc_html_e('Who are you?', 'delta9-digital-blocks-plugin'); ?></p>
				<?php foreach ($audiences as $item) { ?>
					<a class="d9-mega__link" href="<?php echo esc_url($url((string) ($item['url'] ?? '#'))); ?>"><?php echo esc_html((string) ($item['title'] ?? '')); ?></a>
				<?php } ?>
			</div>
			<div class="d9-mega__col d9-mega__col--promo">
				<div class="d9-mega__promo d9-mega__promo--gradient">
					<p class="d9-mega__label d9-mega__label--white"><?php echo esc_html((string) ($promo['eyebrow'] ?? '')); ?></p>
					<p class="d9-mega__promo-title"><?php echo esc_html((string) ($promo['title'] ?? '')); ?></p>
					<p class="d9-mega__promo-text"><?php echo esc_html((string) ($promo['text'] ?? '')); ?></p>
					<?php if (!empty($promo['label'])) { ?>
						<a class="d9-mega__promo-cta" href="<?php echo esc_url($url((string) ($promo['url'] ?? '#'))); ?>"><?php echo esc_html((string) $promo['label']); ?> <span aria-hidden="true">→</span></a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<?php // Work. ?>
	<div class="d9-mega" data-d9-mega-trigger="<?php echo esc_url($triggers['work']); ?>">
		<div class="d9-mega__shell">
			<div class="d9-mega__col">
				<p class="d9-mega__label"><?php esc_html_e('Our projects', 'delta9-digital-blocks-plugin'); ?></p>
				<?php
				$row('▣', [
					'title' => __('All projects', 'delta9-digital-blocks-plugin'),
					/* translators: %d: number of published work posts. */
					'text' => $workCount ? \sprintf(_n('%d launch', '%d launches', $workCount, 'delta9-digital-blocks-plugin'), $workCount) : '',
					'url' => $triggers['work'],
				]);
				?>
				<span class="d9-mega__rule" aria-hidden="true"></span>
				<?php
				foreach (\array_values($projects) as $i => $item) {
					$row(\sprintf('%02d', $i + 1), (array) $item);
				}
				?>
			</div>
			<div class="d9-mega__col">
				<p class="d9-mega__label"><?php esc_html_e('Recent launches', 'delta9-digital-blocks-plugin'); ?></p>
				<?php foreach ($recentWork as $post) { ?>
					<a class="d9-mega__link" href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a>
				<?php } ?>
			</div>
			<?php if ($featuredWork) { ?>
				<?php $featuredStats = \array_slice(\array_values(\array_filter((array) get_post_meta($featuredWork->ID, 'd9_stats', true), 'is_array')), 0, 2); ?>
				<div class="d9-mega__col d9-mega__col--promo">
					<a class="d9-mega__promo" href="<?php echo esc_url(get_permalink($featuredWork)); ?>">
						<span class="d9-mega__label"><?php esc_html_e('Featured case study', 'delta9-digital-blocks-plugin'); ?></span>
						<span class="d9-mega__promo-title"><?php echo esc_html(get_the_title($featuredWork)); ?></span>
						<?php if (has_excerpt($featuredWork)) { ?>
							<span class="d9-mega__promo-text"><?php echo esc_html(get_the_excerpt($featuredWork)); ?></span>
						<?php } ?>
						<?php if ($featuredStats) { ?>
							<span class="d9-mega__stats">
								<?php
								foreach ($featuredStats as $item) {
									$stat((string) ($item['value'] ?? ''), (string) ($item['label'] ?? ''));
								}
								?>
							</span>
						<?php } ?>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php // Integrations. ?>
	<div class="d9-mega" data-d9-mega-trigger="<?php echo esc_url($triggers['integrations']); ?>">
		<div class="d9-mega__shell d9-mega__shell--even">
			<?php foreach ($layerColumns as $column) { ?>
				<div class="d9-mega__col">
					<?php
					foreach ((array) $column as $layerSlug) {
						if (empty($layers[$layerSlug])) {
							continue;
						}
						[$term, $posts] = $layers[$layerSlug];
						?>
						<p class="d9-mega__label"><?php echo esc_html($term->name); ?></p>
						<div class="d9-mega__tiles">
							<?php foreach ($posts as $post) { ?>
								<?php $logoId = (int) get_post_meta($post->ID, 'd9_logo_id', true); ?>
								<a class="d9-mega__tile" href="<?php echo esc_url(get_permalink($post)); ?>">
									<?php
									if ($logoId) {
										echo wp_get_attachment_image($logoId, 'medium', false, ['alt' => get_the_title($post), 'loading' => 'lazy', 'class' => 'd9-mega__tile-logo']);
									} else {
										echo '<span class="d9-mega__tile-name">' . esc_html(get_the_title($post)) . '</span>';
									}
									?>
								</a>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
			<div class="d9-mega__col d9-mega__col--promo">
				<a class="d9-mega__promo" href="<?php echo esc_url($triggers['integrations']); ?>">
					<span class="d9-mega__label"><?php esc_html_e('Integrations hub', 'delta9-digital-blocks-plugin'); ?></span>
					<span class="d9-mega__promo-title"><?php esc_html_e('Menu, POS, loyalty, and Metrc in one build', 'delta9-digital-blocks-plugin'); ?></span>
					<span class="d9-mega__promo-text"><?php esc_html_e('See every platform we connect, what each layer does, and what it takes to wire your stack together.', 'delta9-digital-blocks-plugin'); ?></span>
					<span class="d9-mega__stats">
						<?php
						$stat((string) $integrationCount, __('Platforms', 'delta9-digital-blocks-plugin'));
						$stat((string) \count($layers), __('Layers', 'delta9-digital-blocks-plugin'));
						?>
					</span>
				</a>
			</div>
		</div>
	</div>
</div>
