import React from 'react';
import { useSelect } from '@wordpress/data';
import { overrideInnerBlockSimpleWrapperAttributes } from '@eightshift/frontend-libs/scripts';
import { InspectorControls } from '@wordpress/block-editor';
import { ModalContentEditor } from './components/modal-content-editor';
import { ModalContentOptions } from './components/modal-content-options';

export const ModalContent = (props) => {
	const {
		clientId,
	} = props;

	// Set this attributes to all inner blocks once inserted in DOM.
	useSelect((select) => {
		overrideInnerBlockSimpleWrapperAttributes(select, clientId);
	});

	return (
		<>
			<InspectorControls>
				<ModalContentOptions {...props} />
			</InspectorControls>
			<ModalContentEditor {...props} />
		</>
	);
};
