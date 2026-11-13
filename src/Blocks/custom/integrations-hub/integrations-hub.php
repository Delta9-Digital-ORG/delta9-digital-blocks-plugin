<?php

/**
 * Template for the Integrations Hub Block.
 *
 * One section of the Integrations hub page (reference Integrations.dc.html, variant C with
 * sticky layers and the cascade hero tiles), filled from the `integration` posts (theme data
 * layer: integration_layer, d9_logo_id, d9_accent, d9_layer_label, d9_powers, d9_status).
 * The page stacks one block per section, so the order stays editable. Section markup lives
 * in sections/<section>.php.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';
$section = (string) Helpers::checkAttr('integrationsHubSection', $attributes, $manifest);
$customTitle = (string) Helpers::checkAttr('integrationsHubTitle', $attributes, $manifest);
$customLead = (string) Helpers::checkAttr('integrationsHubLead', $attributes, $manifest);
$ctaLabel = (string) Helpers::checkAttr('integrationsHubCtaLabel', $attributes, $manifest);
$ctaUrl = (string) Helpers::checkAttr('integrationsHubCtaUrl', $attributes, $manifest);
$platformSlugs = \array_values(\array_filter(\array_map('sanitize_title', (array) Helpers::checkAttr('integrationsHubPlatforms', $attributes, $manifest))));
$layerRows = (array) Helpers::checkAttr('integrationsHubLayers', $attributes, $manifest);
$isEditor = Helpers::checkAttr('integrationsHubServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? 'wide'; // Manifest defaults don't reach $attributes for attribute-less template blocks.

$sections = \wp_list_pluck($manifest['options']['integrationsHubSection'] ?? [], 'value');
if (!\in_array($section, $sections, true)) {
	return;
}

if ($ctaUrl !== '' && \str_starts_with($ctaUrl, '/')) {
	$ctaUrl = home_url($ctaUrl);
}

/**
 * Hub layers keyed by integration_layer slug: filter key, table label, accent.
 * Filterable so new layer terms can join the table filter.
 */
$hubLayers = (array) apply_filters('d9_integrations_hub_layers', [
	'menus' => ['key' => 'menu', 'label' => __('Menu', 'delta9-digital-blocks-plugin'), 'accent' => 'mint'],
	'pos' => ['key' => 'pos', 'label' => __('POS', 'delta9-digital-blocks-plugin'), 'accent' => 'cyan'],
	'loyalty' => ['key' => 'loyalty', 'label' => __('Loyalty', 'delta9-digital-blocks-plugin'), 'accent' => 'coral'],
	'data' => ['key' => 'data', 'label' => __('Data', 'delta9-digital-blocks-plugin'), 'accent' => 'yellow'],
	'compliance' => ['key' => 'compliance', 'label' => __('Compliance', 'delta9-digital-blocks-plugin'), 'accent' => 'cyan'],
	'listings' => ['key' => 'listing', 'label' => __('Listing', 'delta9-digital-blocks-plugin'), 'accent' => 'yellow'],
]);

// Every platform in hub order: layer (term order), then position within the layer.
$platforms = [];
foreach (get_posts(['post_type' => 'integration', 'posts_per_page' => 100, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'], 'no_found_rows' => true]) as $item) {
	$terms = get_the_terms($item, 'integration_layer');
	$term = \is_array($terms) ? ($terms[0] ?? null) : null;
	$hub = $hubLayers[$term->slug ?? ''] ?? ['key' => '', 'label' => $term->name ?? '', 'accent' => 'mint'];
	$meta = static fn(string $key) => get_post_meta($item->ID, "d9_{$key}", true);

	$platforms[$item->post_name] = [ // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		'id' => $item->ID,
		'name' => get_the_title($item),
		'url' => get_permalink($item),
		'logoId' => (int) $meta('logo_id'),
		'accent' => \sanitize_key((string) $meta('accent')) ?: 'mint',
		'layerOrder' => $term ? (int) $term->term_id : \PHP_INT_MAX,
		'order' => (int) $item->menu_order, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		'layerLabel' => (string) $meta('layer_label') ?: \html_entity_decode($term->name ?? ''),
		'hub' => $hub,
		'powers' => (string) $meta('powers'),
		'status' => \sanitize_key((string) $meta('status')) ?: 'live',
	];
}
\uasort($platforms, static fn($a, $b) => [$a['layerOrder'], $a['order'], $a['name']] <=> [$b['layerOrder'], $b['order'], $b['name']]);

if (!$platforms) {
	if ($isEditor) {
		echo '<p>' . esc_html__('Add integration posts to fill the hub.', 'delta9-digital-blocks-plugin') . '</p>';
	}
	return;
}

/**
 * Picked platforms (slugs attribute) or the fallback list, in the given order.
 *
 * @param array<int, string> $fallback Default slugs.
 */
$pick = static function (array $fallback) use ($platformSlugs, $platforms): array {
	$slugs = $platformSlugs ?: $fallback;
	return \array_values(\array_filter(\array_map(static fn($slug) => $platforms[$slug] ?? null, $slugs)));
};

/**
 * Platform logo (white) or the platform name.
 */
$logo = static function (array $platform, string $class = ''): void {
	if ($platform['logoId']) {
		echo wp_get_attachment_image($platform['logoId'], 'medium', false, ['class' => \trim("{$class} d9-hub__logo"), 'alt' => $platform['name'], 'loading' => 'lazy']);
		return;
	}
	echo '<span class="' . esc_attr(\trim("{$class} d9-hub__logo-name")) . '">' . esc_html($platform['name']) . '</span>';
};

$accentVar = static fn(string $accent): string => '--d9-hub-accent:var(--wp--preset--color--' . \sanitize_key($accent) . ');';

$sectionClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	'd9-hub',
	"d9-hub--{$section}",
	$align && $section !== 'ribbon' ? "align{$align}" : '', // The ribbon sits in a hero column.
]);

\ob_start();
require __DIR__ . "/sections/{$section}.php";
$inner = \trim((string) \ob_get_clean());

if ($inner === '') {
	return;
}
?>
<section class="<?php echo esc_attr($sectionClass); ?>" data-section="<?php echo esc_attr($section); ?>">
	<?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, Eightshift.Security.ComponentsEscape.OutputNotEscaped -- escaped in the section partials. ?>
</section>
