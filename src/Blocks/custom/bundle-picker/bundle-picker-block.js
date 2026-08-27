import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { BundlePickerEditor } from './components/bundle-picker-editor';
import { BundlePickerOptions } from './components/bundle-picker-options';

export const BundlePicker = (props) => {
	return (
		<>
			<InspectorControls>
				<BundlePickerOptions {...props} />
			</InspectorControls>
			<BundlePickerEditor {...props} />
		</>
	);
};
