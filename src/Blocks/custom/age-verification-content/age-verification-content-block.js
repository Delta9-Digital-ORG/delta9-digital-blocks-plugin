import React from 'react';
import { useSelect } from '@wordpress/data';
import { overrideInnerBlockSimpleWrapperAttributes } from '@eightshift/frontend-libs/scripts';
import { InspectorControls } from '@wordpress/block-editor';
import { AgeVerificationContentEditor } from './components/age-verification-content-editor';
import { AgeVerificationContentOptions } from './components/age-verification-content-options';

export const AgeVerificationContent = (props) => {
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
				<AgeVerificationContentOptions {...props} />
			</InspectorControls>
			<AgeVerificationContentEditor {...props} />
		</>
	);
};
