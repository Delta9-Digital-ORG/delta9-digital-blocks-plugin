import React from 'react';
import { InspectorControls, BlockControls } from '@wordpress/block-editor';
import { ModalEditor } from './components/modal-editor';
import { ModalOptions } from './components/modal-options';
import { ModalToolbar } from './components/modal-toolbar';

export const Modal = (props) => {
	return (
		<>
			<InspectorControls>
				<ModalOptions {...props} />
			</InspectorControls>
			<BlockControls>
				<ModalToolbar {...props} />
			</BlockControls>
			<ModalEditor {...props} />
		</>
	);
};
