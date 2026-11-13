import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FilterSearchEditor } from './components/filter-search-editor';
import { FilterSearchOptions } from './components/filter-search-options';

export const FilterSearch = (props) => {
	return (
		<>
			<InspectorControls>
				<FilterSearchOptions {...props} />
			</InspectorControls>
			<FilterSearchEditor {...props} />
		</>
	);
};
