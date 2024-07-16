import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props, getOptions } from '@eightshift/frontend-libs/scripts';
import { AgeVerificationOptions as AgeVerificationOptionsComponent } from '../../../components/age-verification/components/age-verification-options';
import manifest from './../manifest.json';


export const AgeVerificationOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Age Verification Options', 'delta9-digital-blocks-plugin')}>
				<AgeVerificationOptionsComponent
				{...props('age-verification', attributes, {
					setAttributes,
					options: getOptions(attributes, manifest),
				})}
			/>
		</PanelBody>
	);
};
