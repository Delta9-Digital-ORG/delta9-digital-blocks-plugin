<?php

/**
 * Template for the Integration Section Block.
 *
 * One section of an integration page (reference Integration Detail.dc.html), filled from the
 * current `integration` post (theme data layer: d9_* meta, excerpt, integration_layer). The
 * theme's single-integration template stacks one block per section, so the order stays
 * editable. Section markup lives in sections/<section>.php.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$section = (string) Helpers::checkAttr('integrationSectionSection', $attributes, $manifest);
$customTitle = (string) Helpers::checkAttr('integrationSectionTitle', $attributes, $manifest);
$customLead = (string) Helpers::checkAttr('integrationSectionLead', $attributes, $manifest);
$isEditor = Helpers::checkAttr('integrationSectionServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? 'wide'; // Manifest defaults don't reach $attributes for attribute-less template blocks.

$sections = \wp_list_pluck($manifest['options']['integrationSectionSection'] ?? [], 'value');
if (!\in_array($section, $sections, true)) {
	return;
}

$post = get_post();
if ((!$post || $post->post_type !== 'integration') && $isEditor) {
	$first = get_posts(['post_type' => 'integration', 'posts_per_page' => 1, 'orderby' => 'menu_order title', 'order' => 'ASC']);
	$post = $first[0] ?? null;
}
if (!$post || $post->post_type !== 'integration') {
	if ($isEditor) {
		echo '<p>' . esc_html__('Integration sections show data from an integration post.', 'delta9-digital-blocks-plugin') . '</p>';
	}
	return;
}

$meta = static fn(string $key) => get_post_meta($post->ID, "d9_{$key}", true);
$list = static fn(string $key): array => \array_values(\array_filter((array) $meta($key), static fn($item) => \is_array($item) && \array_filter($item)));

$name = get_the_title($post);
$layers = get_the_terms($post, 'integration_layer');
$layer = \is_array($layers) ? ($layers[0] ?? null) : null;

$data = [
	'post' => $post,
	'name' => $name,
	'h1' => (string) $meta('h1') ?: $name,
	'intro' => has_excerpt($post) ? get_the_excerpt($post) : '',
	'about' => (string) $meta('about'),
	'layerLabel' => (string) $meta('layer_label') ?: ($layer->name ?? ''),
	'layer' => $layer,
	'accent' => \sanitize_key((string) $meta('accent')) ?: 'mint',
	'logoId' => (int) $meta('logo_id'),
	'features' => $list('features'),
	'benefits' => $list('benefits'),
	'steps' => $list('steps'),
	'faq' => $list('faq'),
	'quote' => \array_filter((array) $meta('quote')),
	'relatedIds' => \array_values(\array_filter(\array_map('intval', (array) $meta('related_ids')))),
];

$accentCycle = ['mint', 'coral', 'cyan', 'yellow'];
$hubUrl = (string) apply_filters('d9_integrations_hub_url', home_url('/integrations/'));
$contactUrl = (string) apply_filters('d9_integrations_contact_url', home_url('/contact/'));

/**
 * Brand SVG from the theme (assets/images/brand/), filterable for other themes.
 */
$brand = static function (string $file): string {
	$path = get_theme_file_path("assets/images/brand/{$file}");
	$url = \file_exists($path) ? get_theme_file_uri("assets/images/brand/{$file}") : '';

	return (string) apply_filters('d9_brand_asset_url', $url, $file);
};

/**
 * Platform logo (white) or the platform name.
 */
$logo = static function (string $class) use ($data): void {
	if ($data['logoId']) {
		echo wp_get_attachment_image($data['logoId'], 'medium', false, ['class' => "{$class} d9-int__logo", 'alt' => $data['name'], 'loading' => 'lazy']);
		return;
	}
	echo '<span class="' . esc_attr("{$class} d9-int__logo-name") . '">' . esc_html($data['name']) . '</span>';
};

/**
 * Notch shapes for a panel: page-coloured cut + two fillets, and the pill label.
 */
$notch = static function (string $side, string $pill, string $size = 'panel'): void {
	$side = $side === 'right' ? 'right' : 'left';
	?>
	<span class="d9-int-notch d9-int-notch--<?php echo esc_attr("{$side} d9-int-notch--{$size}"); ?>" aria-hidden="true">
		<span class="d9-int-notch__cut"></span>
		<span class="d9-int-notch__fx"></span>
		<span class="d9-int-notch__fy"></span>
	</span>
	<?php if ($pill) { ?>
		<span class="d9-int-pill d9-int-pill--<?php echo esc_attr("{$side} d9-int-pill--{$size}"); ?>"><?php echo esc_html($pill); ?></span>
	<?php } ?>
	<?php
};

$sectionClass = Helpers::classnames([
	$blockClass,
	'd9-int',
	"d9-int--{$section}",
	$align ? "align{$align}" : '',
]);

\ob_start();
require __DIR__ . "/sections/{$section}.php";
$inner = \trim((string) \ob_get_clean());

if ($inner === '') {
	return;
}
?>
<section class="<?php echo esc_attr($sectionClass); ?>" style="<?php echo esc_attr('--d9-int-accent:var(--wp--preset--color--' . $data['accent'] . ');'); ?>">
	<?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, Eightshift.Security.ComponentsEscape.OutputNotEscaped -- escaped in the section partials. ?>
</section>
