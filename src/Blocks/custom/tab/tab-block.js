import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { TabEditor } from './components/tab-editor';
import { TabOptions } from './components/tab-options';

export const Tab = (props) => {
	return (
		<>
			<InspectorControls>
				<TabOptions {...props} />
			</InspectorControls>
			<TabEditor {...props} />
		</>
	);
};
