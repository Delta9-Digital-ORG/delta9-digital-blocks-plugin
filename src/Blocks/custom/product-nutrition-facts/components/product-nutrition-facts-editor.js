import React from 'react';
import { __ } from '@wordpress/i18n';

// Editor preview — the panel's line labels with placeholder values. Real
// values resolve server-side from the current product's nutrition meta, and
// this block's editing surface (the shared description/nutrition pattern) has
// no product context to read from.
const LINES = [
	['Calories', 0],
	['Total Fat', 0],
	['Saturated Fat', 1],
	['Trans Fat', 1],
	['Cholesterol', 0],
	['Sodium', 0],
	['Total Carbohydrate', 0],
	['Dietary Fiber', 1],
	['Total Sugars', 1],
	['Added Sugars', 2],
	['Protein', 0],
];

export const ProductNutritionFactsEditor = () => (
	<table className='yb-nutrition-facts'>
		<tbody>
			{LINES.map(([label, indent]) => (
				<tr key={label} className={`yb-nutrition-facts__row is-indent-${indent}`}>
					<th scope='row'>{__(label, 'delta9-digital-blocks-plugin')}</th>
					<td>&mdash;</td>
				</tr>
			))}
		</tbody>
	</table>
);
