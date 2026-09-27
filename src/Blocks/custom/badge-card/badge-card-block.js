import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { BadgeCardOptions } from './components/badge-card-options';
import { BadgeCardEditor } from './components/badge-card-editor';

export const BadgeCard = (props) => {
	return (
		<>
			<InspectorControls>
				<BadgeCardOptions {...props} />
			</InspectorControls>
			<BadgeCardEditor {...props} />
		</>
	);
};
