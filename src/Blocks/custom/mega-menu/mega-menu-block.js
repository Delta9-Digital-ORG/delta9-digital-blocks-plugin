import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MegaMenuOptions } from './components/mega-menu-options';
import { MegaMenuEditor } from './components/mega-menu-editor';

export const MegaMenu = (props) => {
	return (
		<>
			<InspectorControls>
				<MegaMenuOptions {...props} />
			</InspectorControls>
			<MegaMenuEditor {...props} />
		</>
	);
};
