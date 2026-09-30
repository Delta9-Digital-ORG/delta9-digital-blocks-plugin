<?php

/**
 * Template for the Case Study Feature Block.
 *
 * Spec: theme claude_design_handoff/design_handoff_wp/blocks/case-study-feature.md.
 * All content comes from a `work` post (theme data layer): d9_* meta for domain, logo,
 * screenshots and stats, `work_service` terms for the tags, the permalink for the link.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';

$workId = (int) Helpers::checkAttr('caseStudyFeatureWorkId', $attributes, $manifest);
$ctaLabel = Helpers::checkAttr('caseStudyFeatureCtaLabel', $attributes, $manifest);
$showPhone = Helpers::checkAttr('caseStudyFeatureShowPhone', $attributes, $manifest);
$isEditor = Helpers::checkAttr('caseStudyFeatureServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? '';
$anchor = $attributes['anchor'] ?? '';

$work = $workId ? get_post($workId) : null;

if (!$work || $work->post_type !== 'work' || ($work->post_status !== 'publish' && !$isEditor)) {
	if ($isEditor) {
		echo '<p>' . esc_html__('The selected work post is not available.', 'delta9-digital-blocks-plugin') . '</p>';
	}
	return;
}

$meta = static fn(string $key) => get_post_meta($work->ID, "d9_{$key}", true);

$title = get_the_title($work);
$domain = (string) $meta('domain');
$logoId = (int) $meta('logo_id');
$desktopId = (int) $meta('desktop_image_id');
$mobileId = (int) $meta('mobile_image_id');
$stats = \array_slice(\array_values(\array_filter((array) $meta('stats'), 'is_array')), 0, 3);

// Service tags, in the design's order, each with its accent. Filter to add or recolor services.
$tagAccents = apply_filters('d9_case_study_tag_accents', [
	'design' => 'mint',
	'development' => 'coral',
	'seo' => 'cyan',
	'e-commerce' => 'yellow',
	'integrations' => 'light',
]);
$accentCycle = ['mint', 'coral', 'cyan', 'yellow', 'light'];
$customTags = \array_values(\array_filter(\array_map('trim', \explode(',', (string) Helpers::checkAttr('caseStudyFeatureTags', $attributes, $manifest)))));
if ($customTags) {
	// Per-page tags (service pages), in the given order; accents from the cycle.
	$terms = \array_map(static fn($name) => (object) ['name' => $name, 'slug' => ''], $customTags);
} else {
	$terms = get_the_terms($work, 'work_service');
	$terms = \is_array($terms) ? $terms : [];
	$slugOrder = \array_flip(\array_keys($tagAccents));
	\usort($terms, static fn($a, $b) => ($slugOrder[$a->slug] ?? \PHP_INT_MAX) <=> ($slugOrder[$b->slug] ?? \PHP_INT_MAX));
}

$sectionClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	'd9-csf',
	$align ? "align{$align}" : '',
	$showPhone && $mobileId ? 'd9-csf--has-phone' : '',
]);

$color = static fn(string $slug) => '--d9-accent:var(--wp--preset--color--' . \sanitize_key($slug) . ');';

?>
<section
	class="<?php echo esc_attr($sectionClass); ?>"
	<?php echo $anchor ? 'id="' . esc_attr($anchor) . '"' : ''; ?>
>
	<div class="d9-csf__stage">
		<a class="d9-csf__card" href="<?php echo esc_url(get_permalink($work)); ?>">
			<span class="d9-csf__chrome" aria-hidden="true"><span></span><span></span><span></span></span>
			<span class="d9-csf__frame" data-d9-pan-frame>
				<?php
				if ($desktopId) {
					echo wp_get_attachment_image($desktopId, 'full', false, [
						'class' => 'd9-csf__shot',
						'data-d9-pan-img' => '',
						'sizes' => '(max-width: 1528px) 100vw, 1480px',
						'loading' => 'lazy',
					]);
				}
				?>
			</span>

			<span class="d9-csf__caption">
				<span class="d9-csf__glass" aria-hidden="true"></span>
				<span class="d9-csf__row">
					<span class="d9-csf__cta">
						<?php echo esc_html($ctaLabel); ?>
						<span class="screen-reader-text"><?php echo esc_html(': ' . $title); ?></span>
						<span aria-hidden="true">→</span>
					</span>
					<?php if ($terms) { ?>
						<span class="d9-csf__tags">
							<?php foreach ($terms as $i => $term) { ?>
								<span class="d9-csf__tag" style="<?php echo esc_attr($color($tagAccents[$term->slug] ?? $accentCycle[$i % \count($accentCycle)])); ?>"><?php echo esc_html($term->name); ?></span>
							<?php } ?>
						</span>
					<?php } ?>
				</span>

				<span class="d9-csf__meta">
					<span class="d9-csf__client">
						<?php
						if ($logoId) {
							echo wp_get_attachment_image($logoId, 'full', false, [
								'class' => 'd9-csf__logo',
								'alt' => $title,
								'loading' => 'lazy',
							]);
						} else {
							echo '<span class="d9-csf__name">' . esc_html($title) . '</span>';
						}
						?>
						<?php if ($domain) { ?>
							<span class="d9-csf__domain"><?php echo esc_html($domain); ?></span>
						<?php } ?>
					</span>

					<?php if ($stats) { ?>
						<span class="d9-csf__stats">
							<?php foreach ($stats as $i => $stat) { ?>
								<span class="d9-csf__stat" style="<?php echo esc_attr($color((string) ($stat['accent'] ?? '') ?: $accentCycle[$i])); ?>">
									<span class="d9-csf__stat-value"><?php echo esc_html((string) ($stat['value'] ?? '')); ?></span>
									<span class="d9-csf__stat-label"><?php echo esc_html((string) ($stat['label'] ?? '')); ?></span>
								</span>
							<?php } ?>
						</span>
					<?php } ?>
				</span>
			</span>
		</a>

		<?php if ($showPhone && $mobileId) { ?>
			<div class="d9-csf__phone">
				<div class="d9-csf__screen">
					<span class="d9-csf__notch" aria-hidden="true"></span>
					<?php
					echo wp_get_attachment_image($mobileId, 'full', false, [
						'class' => 'd9-csf__phone-shot',
						'sizes' => '290px',
						'loading' => 'lazy',
					]);
					?>
				</div>
			</div>
		<?php } ?>
	</div>
</section>
