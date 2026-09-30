<?php

/**
 * Template for the Post Hero Block (handoff design_handoff_blog_post, sections 3 and 4).
 *
 * Full-bleed featured image under a gradient, with a glass title card hanging 150px below
 * it: category pill in a bottom-left notch, share button in a top-right notch, title, tag
 * pills and date / author / reading-time pills. The share menu is progressive: links work
 * without JS; assets/share.js adds the toggle, copy-link and closing behaviour.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPlugin\Insights\InsightsHelper;
use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? '';
$showShare = Helpers::checkAttr('postHeroShowShare', $attributes, $manifest);
$isEditor = Helpers::checkAttr('postHeroServerSideRender', $attributes, $manifest);
$align = $attributes['align'] ?? 'full'; // Manifest defaults don't reach $attributes when the block has no saved attributes.

$post = get_post();
if (!$post && $isEditor) {
	$latest = get_posts(['post_type' => 'post', 'posts_per_page' => 1]);
	$post = $latest[0] ?? null;
}
if (!$post) {
	return;
}

$title = get_the_title($post);
$permalink = (string) get_permalink($post);
$primary = InsightsHelper::primaryCategory($post);
$tags = InsightsHelper::tags($post, 4);
$author = InsightsHelper::author($post);
$minutes = InsightsHelper::readingMinutes($post);

$shareUrl = \rawurlencode($permalink);
$shareTitle = \rawurlencode(wp_strip_all_tags($title));
$fbAppId = (string) apply_filters('d9_share_facebook_app_id', '');

// [label, url, icon path(s), filled icon].
$shareLinks = [
	['Facebook', "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}", '<path d="M24 12a12 12 0 1 0-13.9 11.9v-8.4H7.1V12h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9V12h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12z"/>', true],
];
if ($fbAppId) {
	$shareLinks[] = ['Messenger', 'https://www.facebook.com/dialog/send?app_id=' . \rawurlencode($fbAppId) . "&link={$shareUrl}&redirect_uri={$shareUrl}", '<path d="M12 2C6.4 2 2 6.1 2 11.7c0 2.9 1.2 5.5 3.2 7.3V22l3-1.6c1.2.3 2.4.5 3.8.5 5.6 0 10-4.1 10-9.7S17.6 2 12 2zm1 13-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7L13 15z"/>', true];
}
$shareLinks[] = ['Instagram', 'https://instagram.com/delta9.digital', '<rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.6" fill="currentColor"/>', false];
$shareLinks[] = ['X', "https://x.com/intent/post?url={$shareUrl}&text={$shareTitle}", '<path d="M18.9 2H22l-7.5 8.6L23 22h-6.8l-5.3-6.9L4.8 22H1.7l8-9.2L1 2h7l4.8 6.3L18.9 2zm-1.2 18h1.7L7.4 3.9H5.6L17.7 20z"/>', true];
$shareLinks[] = ['LinkedIn', "https://www.linkedin.com/sharing/share-offsite/?url={$shareUrl}", '<path d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM7.1 20.5H3.5V9h3.6v11.5z"/>', true];
$shareLinks[] = ['Discord', 'https://discord.com/app', '<path d="M20.3 4.4A19.8 19.8 0 0 0 15.4 3l-.6 1.3a18.4 18.4 0 0 0-5.6 0L8.6 3a19.7 19.7 0 0 0-4.9 1.5C.6 9.1-.3 13.7.1 18.2a19.9 19.9 0 0 0 6 3l1.3-2.1c-.7-.3-1.4-.6-2-1l.5-.4a14.2 14.2 0 0 0 12.2 0l.5.4c-.6.4-1.3.7-2 1l1.3 2.1a19.8 19.8 0 0 0 6-3c.5-5.2-.8-9.8-3.6-13.8zM8 15.4c-1.2 0-2.2-1.1-2.2-2.4S6.8 10.6 8 10.6s2.2 1.1 2.2 2.4-1 2.4-2.2 2.4zm8 0c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4z"/>', true];

$svgAllowed = [
	'svg' => ['width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'class' => true],
	'path' => ['d' => true, 'fill' => true],
	'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true],
	'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true],
];
$icon = static function (string $paths, bool $filled) use ($svgAllowed): string {
	$attrs = $filled
		? 'fill="currentColor"'
		: 'fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
	return wp_kses("<svg class=\"d9-share__icon\" width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" {$attrs} aria-hidden=\"true\">{$paths}</svg>", $svgAllowed);
};

$heroClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	'd9-post-hero',
	$align ? "align{$align}" : '',
	has_post_thumbnail($post) ? '' : 'd9-post-hero--no-image',
]);

?>
<header class="<?php echo esc_attr($heroClass); ?>">
	<div class="d9-post-hero__media">
		<?php
		if (has_post_thumbnail($post)) {
			echo get_the_post_thumbnail($post, 'full', ['class' => 'd9-post-hero__image', 'alt' => '', 'sizes' => '100vw', 'fetchpriority' => 'high']);
		}
		?>
		<span class="d9-post-hero__shade" aria-hidden="true"></span>
	</div>

	<div class="d9-post-hero__card-wrap">
		<div class="d9-post-hero__card">
			<span class="d9-post-hero__glass" aria-hidden="true"></span>

			<?php if ($showShare) { ?>
				<div class="d9-share">
					<button type="button" class="d9-share__toggle" aria-label="<?php esc_attr_e('Share this post', 'delta9-digital-blocks-plugin'); ?>" aria-haspopup="menu" aria-expanded="false" hidden>
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4"/><path d="m15.4 6.5-6.8 4"/></svg>
					</button>
					<div class="d9-share__menu" role="menu" aria-label="<?php esc_attr_e('Share', 'delta9-digital-blocks-plugin'); ?>">
						<button type="button" class="d9-share__item d9-share__copy" role="menuitem" data-url="<?php echo esc_url($permalink); ?>" data-copied="<?php esc_attr_e('Link copied', 'delta9-digital-blocks-plugin'); ?>">
							<?php echo $icon('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>', false); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
							<span class="d9-share__label"><?php esc_html_e('Copy link', 'delta9-digital-blocks-plugin'); ?></span>
						</button>
						<?php foreach ($shareLinks as [$shareLabel, $shareHref, $paths, $filled]) { ?>
							<a class="d9-share__item" role="menuitem" href="<?php echo esc_url($shareHref); ?>" target="_blank" rel="noopener">
								<?php echo $icon($paths, $filled); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
								<span class="d9-share__label"><?php echo esc_html($shareLabel); ?></span>
							</a>
						<?php } ?>
					</div>
				</div>
			<?php } ?>

			<?php if ($primary) { ?>
				<a class="d9-post-hero__category" href="<?php echo esc_url(get_category_link($primary)); ?>"><?php echo esc_html($primary->name); ?></a>
			<?php } ?>

			<h1 class="d9-post-hero__title"><?php echo esc_html($title); ?></h1>

			<div class="d9-post-hero__meta">
				<?php if ($tags) { ?>
					<span class="d9-post-hero__meta-label"><?php esc_html_e('Tags', 'delta9-digital-blocks-plugin'); ?></span>
					<?php foreach ($tags as $tag) { ?>
						<a class="d9-post-hero__tag" href="<?php echo esc_url(get_tag_link($tag)); ?>"><?php echo esc_html($tag->name); ?></a>
					<?php } ?>
				<?php } ?>
				<span class="d9-post-hero__facts">
					<time class="d9-post-hero__fact" datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html(get_the_date('M j, Y', $post)); ?></time>
					<?php if ($author['name']) { ?>
						<a class="d9-post-hero__fact" href="<?php echo esc_url($author['url']); ?>"><?php echo esc_html($author['name']); ?></a>
					<?php } ?>
					<span class="d9-post-hero__fact">
						<?php
						/* translators: %d: minutes. */
						echo esc_html(\sprintf(_n('%d min read', '%d min read', $minutes, 'delta9-digital-blocks-plugin'), $minutes));
						?>
					</span>
				</span>
			</div>
		</div>
	</div>
</header>
