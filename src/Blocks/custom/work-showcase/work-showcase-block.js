import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { WorkShowcaseOptions } from './components/work-showcase-options';
import { WorkShowcaseEditor } from './components/work-showcase-editor';

export const WorkShowcase = (props) => {
	return (
		<>
			<InspectorControls>
				<WorkShowcaseOptions {...props} />
			</InspectorControls>
			<WorkShowcaseEditor {...props} />
		</>
	);
};
