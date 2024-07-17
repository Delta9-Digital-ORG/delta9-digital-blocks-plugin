import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, getOptions,IconToggle  } from '@eightshift/frontend-libs/scripts';
import { ModalButtonOptions } from '../../../components/modal-button/components/modal-button-options';
import { ModalOptions as ModalOptionsComponent } from '../../../components/modal/components/modal-options';
import manifest from '../manifest.json';


export const ModalContentOptions = ({ attributes, setAttributes }) => {
	const modalContentStartOpen = checkAttr('modalContentStartOpen', attributes, manifest);

	return (
		<PanelBody title={__('ModalContent', 'delta9-digital-blocks')}>
			<IconToggle
				icon={icons.dropdownClose}
				label={__('Open', 'delta9-digital-blocks')}
				checked={modalContentStartOpen}
				onChange={(value) => setAttributes({ [getAttrKey('modalContentStartOpen', attributes, manifest)]: value })}
				noBottomSpacing
			/>

			<Section label={__('Other', 'delta9-digital-blocks')} icon={icons.moreH} noBottomSpacing>
				<ModalButtonOptions
					{...props('modal-button', attributes, {
						setAttributes,
						options: getOptions(attributes, manifest),
					})}
					label={__('"Modal" button', 'delta9-digital-blocks')}
					noBottomSpacing
				/>
			</Section>
			
			<Section label={__('Modal', 'delta9-digital-blocks')}>
				<ModalOptionsComponent
					{...props('modal', attributes, {
						setAttributes,
						options: getOptions(attributes, manifest),
					})}
				/>
			</Section>
		</PanelBody>
	);
};
