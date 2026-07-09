<?php

/**
 * Template-part theme reference guard.
 *
 * Site Editor saves of `wp_template` / `wp_template_part` / `wp_block`
 * posts can stamp `"theme":"<old-theme-slug>"` onto every nested
 * <!-- wp:template-part /--> block. WordPress can only resolve template
 * parts that match the *active* theme, so header / footer / etc. parts
 * silently render to nothing when the stored theme is wrong.
 *
 * This service rewrites any stale theme attribute on save (and on first
 * render via `render_block_data`) so the active theme always wins.
 *
 * @package Delta9DigitalBlocksPlugin\TemplateThemeFix
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\TemplateThemeFix;

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Class TemplateThemeFix
 */
class TemplateThemeFix implements ServiceInterface
{
	/**
	 * Post types whose post_content gets the theme-reference rewrite.
	 */
	private const REWRITE_POST_TYPES = [
		'wp_template',
		'wp_template_part',
		'wp_block',
	];

	/**
	 * @inheritDoc
	 */
	public function register(): void
	{
		// Rewrite stale theme refs whenever a Site-Editor-managed post is
		// saved (template, template part, or synced/reusable block).
		\add_filter('wp_insert_post_data', [$this, 'rewriteOnSave'], 10, 2);

		// Defensive: also rewrite the in-memory parsed block tree at render
		// time so a stale stored value still resolves correctly until the
		// post gets re-saved.
		\add_filter('render_block_data', [$this, 'rewriteOnRender'], 10, 1);
	}

	/**
	 * Replace stale theme="..." attributes with the active theme on save.
	 *
	 * @param array<string, mixed> $data    Slashed post data.
	 * @param array<string, mixed> $postArr Sanitized post data (unused).
	 *
	 * @return array<string, mixed>
	 */
	public function rewriteOnSave(array $data, array $postArr): array
	{
		if (!isset($data['post_type']) || !\in_array($data['post_type'], self::REWRITE_POST_TYPES, true)) {
			return $data;
		}

		$content = $data['post_content'] ?? '';
		if (!\is_string($content) || '' === $content) {
			return $data;
		}

		$activeTheme = \get_stylesheet();

		// Match "theme":"<anything-but-the-active-theme>" inside any block
		// comment. WP's parsed serialized form always uses
		// JSON-style attributes, so a plain regex is reliable here.
		$rewritten = \preg_replace_callback(
			'/"theme":"([^"\\\\]+)"/',
			static function ($m) use ($activeTheme) {
				if ($m[1] === $activeTheme) {
					return $m[0];
				}
				return '"theme":"' . $activeTheme . '"';
			},
			$content
		);

		if (\is_string($rewritten) && $rewritten !== $content) {
			$data['post_content'] = \wp_slash($rewritten);
		}

		return $data;
	}

	/**
	 * Fix template-part theme attribute on the parsed block tree at render
	 * time so stale data renders correctly without requiring a re-save.
	 *
	 * @param array<string, mixed> $parsedBlock Parsed block array.
	 *
	 * @return array<string, mixed>
	 */
	public function rewriteOnRender(array $parsedBlock): array
	{
		if (!isset($parsedBlock['blockName']) || 'core/template-part' !== $parsedBlock['blockName']) {
			return $parsedBlock;
		}

		if (!isset($parsedBlock['attrs']) || !\is_array($parsedBlock['attrs'])) {
			return $parsedBlock;
		}

		if (!isset($parsedBlock['attrs']['theme']) || !\is_string($parsedBlock['attrs']['theme'])) {
			return $parsedBlock;
		}

		$activeTheme = \get_stylesheet();
		if ($parsedBlock['attrs']['theme'] === $activeTheme) {
			return $parsedBlock;
		}

		$parsedBlock['attrs']['theme'] = $activeTheme;
		return $parsedBlock;
	}
}
