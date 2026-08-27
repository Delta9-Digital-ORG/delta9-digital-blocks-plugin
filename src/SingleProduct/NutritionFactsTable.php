<?php

/**
 * Shared renderer for the FDA nutrition panel.
 *
 * The same grid appears twice on a product page — in the single-product
 * block's "Nutritional facts" tab and in the description row's nutrition
 * card — so the markup lives here rather than being written out twice and
 * drifting.
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\SingleProduct;

/**
 * NutritionFactsTable class.
 */
final class NutritionFactsTable
{
	/**
	 * Render the panel.
	 *
	 * Structural styling hangs off `yb-nutrition-facts`; colours are
	 * inherited, so the flavor-themed hero and the green card each supply
	 * their own without this markup knowing about either.
	 *
	 * @param array<int, array{label: string, value: string, indent: int}> $rows  Rows from SingleProductData::nutritionFacts().
	 * @param string                                                       $class Extra class names for the table element.
	 * @param string                                                       $attrs Extra attributes, already escaped.
	 *
	 * @return string Table markup, or an empty string when there is nothing to show.
	 */
	public static function render(array $rows, string $class = '', string $attrs = ''): string
	{
		if (empty($rows)) {
			return '';
		}

		$classes = \trim('yb-nutrition-facts ' . $class);

		$out = \sprintf(
			'<table class="%s"%s>',
			\esc_attr($classes),
			'' === $attrs ? '' : ' ' . $attrs
		);

		$out .= '<caption class="screen-reader-text">'
			. \esc_html__('Nutrition facts', 'delta9-digital-blocks-plugin')
			. '</caption><tbody>';

		foreach ($rows as $row) {
			$out .= \sprintf(
				'<tr class="yb-nutrition-facts__row is-indent-%d"><th scope="row">%s</th><td>%s</td></tr>',
				(int) $row['indent'],
				\esc_html((string) $row['label']),
				\esc_html((string) $row['value'])
			);
		}

		return $out . '</tbody></table>';
	}
}
