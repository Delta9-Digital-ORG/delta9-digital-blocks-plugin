import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { DropdownPickerEditor } from './components/dropdown-picker-editor';
import { DropdownPickerOptions } from './components/dropdown-picker-options';

export const DropdownPicker = (props) => {
	return (
		<>
			<InspectorControls>
				<DropdownPickerOptions {...props} />
			</InspectorControls>
			<DropdownPickerEditor {...props} />
		</>
	);
};
