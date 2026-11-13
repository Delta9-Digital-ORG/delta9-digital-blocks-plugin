import { classnames } from '@eightshift/frontend-libs/scripts';

/**
 * Shared grid class + inline CSS variables for the editor, mirroring badge-cards.php.
 */
export const badgeCardsGridProps = ({ blockClass, columns, variant, surface, cycle }) => ({
	className: classnames(
		blockClass,
		'd9-bcards',
		`d9-bcards--${variant}`,
		`d9-bcards--on-${surface}`,
	),
	style: {
		'--d9-bcards-cols': columns,
		...Object.fromEntries(cycle.slice(0, 4).map((slug, i) => [`--d9-bcards-accent-${i + 1}`, `var(--wp--preset--color--${slug})`])),
	},
});
