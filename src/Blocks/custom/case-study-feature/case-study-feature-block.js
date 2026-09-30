import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CaseStudyFeatureOptions } from './components/case-study-feature-options';
import { CaseStudyFeatureEditor } from './components/case-study-feature-editor';

export const CaseStudyFeature = (props) => {
	return (
		<>
			<InspectorControls>
				<CaseStudyFeatureOptions {...props} />
			</InspectorControls>
			<CaseStudyFeatureEditor {...props} />
		</>
	);
};
