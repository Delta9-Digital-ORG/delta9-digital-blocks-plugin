import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { NotchPanelOptions } from './components/notch-panel-options';
import { NotchPanelEditor } from './components/notch-panel-editor';

export const NotchPanel = (props) => {
	return (
		<>
			<InspectorControls>
				<NotchPanelOptions {...props} />
			</InspectorControls>
			<NotchPanelEditor {...props} />
		</>
	);
};
