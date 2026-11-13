import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FilterChipsEditor } from './components/filter-chips-editor';
import { FilterChipsOptions } from './components/filter-chips-options';

export const FilterChips = (props) => {
	return (
		<>
			<InspectorControls>
				<FilterChipsOptions {...props} />
			</InspectorControls>
			<FilterChipsEditor {...props} />
		</>
	);
};
