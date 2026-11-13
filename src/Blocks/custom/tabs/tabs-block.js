import React from 'react';
import { useSelect } from '@wordpress/data';
import { overrideInnerBlockAttributes } from '@eightshift/frontend-libs/scripts';
import { InspectorControls } from '@wordpress/block-editor';
import { TabsEditor } from './components/tabs-editor';
import { TabsOptions } from './components/tabs-options';

export const Tabs = (props) => {
	// Set these attributes on all inner blocks once inserted.
	useSelect((select) => {
		overrideInnerBlockAttributes(
			select,
			props.clientId,
			{
				wrapperUse: false,
				wrapperNoControls: true,
			},
		);
	});

	return (
		<>
			<InspectorControls>
				<TabsOptions {...props} />
			</InspectorControls>
			<TabsEditor {...props} />
		</>
	);
};
