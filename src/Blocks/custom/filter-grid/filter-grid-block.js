import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FilterGridEditor } from './components/filter-grid-editor';
import { FilterGridOptions } from './components/filter-grid-options';

export const FilterGrid = (props) => {
	return (
		<>
			<InspectorControls>
				<FilterGridOptions {...props} />
			</InspectorControls>
			<FilterGridEditor {...props} />
		</>
	);
};
