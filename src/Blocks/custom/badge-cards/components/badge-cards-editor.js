import React from 'react';
import { useInnerBlocksProps } from '@wordpress/block-editor';
import { BlockInserter, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { badgeCardsGridProps } from './badge-cards-helpers';

export const BadgeCardsEditor = ({ attributes, clientId }) => {
	const { blockClass } = attributes;

	const gridProps = badgeCardsGridProps({
		blockClass,
		columns: checkAttr('badgeCardsColumns', attributes, manifest),
		variant: checkAttr('badgeCardsVariant', attributes, manifest),
		surface: checkAttr('badgeCardsSurface', attributes, manifest),
		cycle: checkAttr('badgeCardsAccentCycle', attributes, manifest),
	});

	const innerBlocksProps = useInnerBlocksProps(gridProps, {
		allowedBlocks: checkAttr('badgeCardsAllowedBlocks', attributes, manifest),
		template: [['eightshift-boilerplate/badge-card'], ['eightshift-boilerplate/badge-card']],
		orientation: 'horizontal',
		renderAppender: () => <BlockInserter clientId={clientId} hasLabel />,
	});

	return <div {...innerBlocksProps} />;
};
