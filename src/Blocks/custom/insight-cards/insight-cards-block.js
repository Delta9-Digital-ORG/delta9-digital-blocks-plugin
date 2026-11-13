import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { InsightCardsOptions } from './components/insight-cards-options';
import { InsightCardsEditor } from './components/insight-cards-editor';

export const InsightCards = (props) => {
	return (
		<>
			<InspectorControls>
				<InsightCardsOptions {...props} />
			</InspectorControls>
			<InsightCardsEditor {...props} />
		</>
	);
};
