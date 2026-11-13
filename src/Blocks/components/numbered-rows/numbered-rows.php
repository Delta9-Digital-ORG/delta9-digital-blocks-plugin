<?php

/**
 * Numbered Rows component: the hairline-separated numbered list of the service pages (design
 * handoff round 2, blocks/numbered-rows.md). Rendered by the Numbered Rows block and inside
 * Sticky Steps and Feature Rows.
 *
 * Sizes: xl (lg gutter, h4 titles, body1 copy; Digital Marketing "Content strategy"), lg, md, sm.
 * Two columns fill column-first; with columns = 2 and several accents, each column takes one
 * accent (the build panel's mint / coral).
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$rows = \array_values(\array_filter((array) Helpers::checkAttr('numberedRowsRows', $attributes, $manifest), static fn($row) => \is_array($row) && (!empty($row['title']) || !empty($row['body']))));
if (!$rows) {
	return;
}

$size = (string) Helpers::checkAttr('numberedRowsSize', $attributes, $manifest);
$size = \in_array($size, ['xl', 'lg', 'md', 'sm'], true) ? $size : 'md';
$columns = (int) Helpers::checkAttr('numberedRowsColumns', $attributes, $manifest) === 2 ? 2 : 1;
$accent = \sanitize_key((string) Helpers::checkAttr('numberedRowsAccent', $attributes, $manifest));
$accents = \array_values(\array_filter(\array_map('sanitize_key', (array) Helpers::checkAttr('numberedRowsAccents', $attributes, $manifest)))) ?: ['mint'];
$startAt = \max(0, (int) Helpers::checkAttr('numberedRowsStartAt', $attributes, $manifest));
$hairlineBottom = (bool) Helpers::checkAttr('numberedRowsHairlineBottom', $attributes, $manifest);

$perColumn = (int) \ceil(\count($rows) / $columns);

$listClass = Helpers::classnames([
	'd9-rows',
	"d9-rows--{$size}",
	"d9-rows--cols-{$columns}",
	$hairlineBottom ? 'd9-rows--closed' : '',
]);
$listStyle = $columns === 2 ? "--rows-per-col:{$perColumn};" : '';

?>
<ol class="<?php echo esc_attr($listClass); ?>"<?php echo $listStyle ? ' style="' . esc_attr($listStyle) . '"' : ''; ?> start="<?php echo esc_attr((string) $startAt); ?>">
	<?php foreach ($rows as $i => $row) { ?>
		<?php
		if ($accent) {
			$rowAccent = $accent;
		} elseif (!empty($row['accent'])) {
			$rowAccent = \sanitize_key((string) $row['accent']);
		} elseif ($columns === 2 && \count($accents) === 2) {
			$rowAccent = $accents[$i < $perColumn ? 0 : 1];
		} else {
			$rowAccent = $accents[$i % \count($accents)];
		}
		$isColumnEnd = $columns === 2 && ($i === $perColumn - 1 || $i === \count($rows) - 1);
		?>
		<li class="d9-rows__row<?php echo $isColumnEnd ? ' d9-rows__row--col-end' : ''; ?>" style="<?php echo esc_attr("--accent:var(--wp--preset--color--{$rowAccent});"); ?>">
			<span class="d9-rows__num" aria-hidden="true"><?php echo esc_html(\str_pad((string) ($startAt + $i), 2, '0', \STR_PAD_LEFT)); ?></span>
			<div class="d9-rows__text">
				<?php if (!empty($row['title'])) { ?>
					<h3 class="d9-rows__title"><?php echo esc_html((string) $row['title']); ?></h3>
				<?php } ?>
				<?php if (!empty($row['body'])) { ?>
					<p class="d9-rows__body"><?php echo esc_html((string) $row['body']); ?></p>
				<?php } ?>
			</div>
		</li>
	<?php } ?>
</ol>
