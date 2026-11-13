import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { BadgeCardsOptions } from './components/badge-cards-options';
import { BadgeCardsEditor } from './components/badge-cards-editor';

export const BadgeCards = (props) => {
	return (
		<>
			<InspectorControls>
				<BadgeCardsOptions {...props} />
			</InspectorControls>
			<BadgeCardsEditor {...props} />
		</>
	);
};
