import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ScrollStoryEditor } from './components/scroll-story-editor';
import { ScrollStoryOptions } from './components/scroll-story-options';

export const ScrollStory = (props) => {
	return (
		<>
			<InspectorControls>
				<ScrollStoryOptions {...props} />
			</InspectorControls>
			<ScrollStoryEditor {...props} />
		</>
	);
};
