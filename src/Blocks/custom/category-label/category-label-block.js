import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CategoryLabelEditor } from './components/category-label-editor';
import { CategoryLabelOptions } from './components/category-label-options';

export const CategoryLabel = (props) => {
	return (
		<>
			<InspectorControls>
				<CategoryLabelOptions {...props} />
			</InspectorControls>
			<CategoryLabelEditor {...props} />
		</>
	);
};
