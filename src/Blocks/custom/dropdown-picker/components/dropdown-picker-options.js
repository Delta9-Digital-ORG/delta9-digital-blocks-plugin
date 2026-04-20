import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const DropdownPickerOptions = ({ attributes, setAttributes }) => {
	const dropdownPickerAlign = checkAttr('dropdownPickerAlign', attributes, manifest);

	return (
		<PanelBody title={__('Dropdown Picker', 'delta9-digital-blocks-plugin')}>
			<SelectControl
				label={__('Alignment', 'delta9-digital-blocks-plugin')}
				value={dropdownPickerAlign}
				options={[
					{ label: __('Left', 'delta9-digital-blocks-plugin'), value: 'left' },
					{ label: __('Center', 'delta9-digital-blocks-plugin'), value: 'center' },
					{ label: __('Right', 'delta9-digital-blocks-plugin'), value: 'right' },
				]}
				onChange={(value) => setAttributes({ [getAttrKey('dropdownPickerAlign', attributes, manifest)]: value })}
			/>
		</PanelBody>
	);
};
