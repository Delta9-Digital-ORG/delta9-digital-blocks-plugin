import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, getOptions,IconToggle  } from '@eightshift/frontend-libs/scripts';
import { AgeVerificationButtonOptions } from '../../../components/age-verification-button/components/age-verification-button-options';
import manifest from '../manifest.json';


export const AgeVerificationContentOptions = ({ attributes, setAttributes }) => {
	const ageVerificationContentStartOpen = checkAttr('ageVerificationContentStartOpen', attributes, manifest);

	return (
		<PanelBody title={__('Age Verification Content', 'delta9-digital-blocks-plugin')}>
			<IconToggle
				icon={icons.dropdownClose}
				label={__('Open', 'delta9-digital-blocks-plugin')}
				checked={ageVerificationContentStartOpen}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationContentStartOpen', attributes, manifest)]: value })}
				noBottomSpacing
			/>

			<Section label={__('Other', 'delta9-digital-blocks-plugin')} icon={icons.moreH} noBottomSpacing>
				<AgeVerificationButtonOptions
					{...props('age-verification-button', attributes, {
						setAttributes,
						options: getOptions(attributes, manifest),
					})}
					label={__('"Age Verification" button', 'delta9-digital-blocks-plugin')}
					noBottomSpacing
				/>
			</Section>
		</PanelBody>
	);
};
