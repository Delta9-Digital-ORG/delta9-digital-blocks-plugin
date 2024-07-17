import React from 'react';
import { InspectorControls, BlockControls } from '@wordpress/block-editor';
import { AgeVerificationEditor } from './components/age-verification-editor';
import { AgeVerificationOptions } from './components/age-verification-options';
import { AgeVerificationToolbar } from './components/age-verification-toolbar';

export const AgeVerification = (props) => {
	return (
		<>
			<InspectorControls>
				<AgeVerificationOptions {...props} />
			</InspectorControls>
			<BlockControls>
				<AgeVerificationToolbar {...props} />
			</BlockControls>
			<AgeVerificationEditor {...props} />
		</>
	);
};
