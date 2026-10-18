import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { EmbedFrameEditor } from './components/embed-frame-editor';
import { EmbedFrameOptions } from './components/embed-frame-options';

export const EmbedFrame = (props) => {
	return (
		<>
			<InspectorControls>
				<EmbedFrameOptions {...props} />
			</InspectorControls>
			<EmbedFrameEditor {...props} />
		</>
	);
};
