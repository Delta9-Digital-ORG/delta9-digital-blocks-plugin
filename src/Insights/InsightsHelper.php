<?php

/**
 * Shared post data for the Insights blocks (insight cards, archive, post hero, related posts).
 *
 * @package Delta9DigitalBlocksPlugin\Insights
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Insights;

use WP_Post;
use WP_Term;

/**
 * Class InsightsHelper
 */
final class InsightsHelper
{
	/**
	 * Accent cycle used when a category has no mapped accent.
	 *
	 * @var array<int, string>
	 */
	public const ACCENT_CYCLE = ['mint', 'coral', 'cyan', 'yellow'];

	/**
	 * Category slug => palette slug (filter `d9_insight_category_accents`).
	 *
	 * @return array<string, string>
	 */
	public static function categoryAccents(): array
	{
		return (array) \apply_filters('d9_insight_category_accents', [
			'compliance' => 'mint',
			'design' => 'coral',
			'web-design' => 'coral',
			'seo' => 'cyan',
			'integrations' => 'yellow',
			'marketing' => 'yellow',
			'branding' => 'purple',
		]);
	}

	/**
	 * First category of a post.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return WP_Term|null
	 */
	public static function primaryCategory(WP_Post $post): ?WP_Term
	{
		$categories = \get_the_category($post->ID);

		return $categories[0] ?? null;
	}

	/**
	 * Accent palette slug for a post's primary category.
	 *
	 * @param WP_Post $post Post.
	 * @param int $index Position, for the cycle fallback.
	 * @param array<string, string> $map Optional map overriding the default.
	 *
	 * @return string
	 */
	public static function accent(WP_Post $post, int $index = 0, array $map = []): string
	{
		$primary = self::primaryCategory($post);
		$map = $map ?: self::categoryAccents();

		if (!$primary) {
			return 'mint';
		}

		return \sanitize_key($map[$primary->slug] ?? self::ACCENT_CYCLE[$index % \count(self::ACCENT_CYCLE)]);
	}

	/**
	 * Reading time in minutes (200 wpm, at least 1).
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return int
	 */
	public static function readingMinutes(WP_Post $post): int
	{
		return \max(1, (int) \ceil(\str_word_count(\wp_strip_all_tags((string) $post->post_content)) / 200));
	}

	/**
	 * Author chip data: name, role (user meta d9_author_role), avatar id (d9_avatar_id), initials.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return array{name: string, role: string, avatarId: int, initials: string, url: string}
	 */
	public static function author(WP_Post $post): array
	{
		$authorId = (int) $post->post_author; // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		$name = (string) \get_the_author_meta('display_name', $authorId);

		return [
			'name' => $name,
			'role' => (string) \get_user_meta($authorId, 'd9_author_role', true),
			'avatarId' => (int) \get_user_meta($authorId, 'd9_avatar_id', true),
			'initials' => \implode('', \array_map(
				static fn($part) => \mb_strtoupper(\mb_substr($part, 0, 1)),
				\array_slice(\preg_split('/\s+/', \trim($name)) ?: [], 0, 2)
			)),
			'url' => $authorId ? (string) \get_author_posts_url($authorId) : '',
		];
	}

	/**
	 * Up to $limit tags.
	 *
	 * @param WP_Post $post Post.
	 * @param int $limit Max tags.
	 *
	 * @return array<int, WP_Term>
	 */
	public static function tags(WP_Post $post, int $limit = 3): array
	{
		$tags = \get_the_tags($post->ID);

		return \is_array($tags) ? \array_slice($tags, 0, $limit) : [];
	}
}
