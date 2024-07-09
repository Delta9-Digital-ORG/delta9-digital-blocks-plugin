import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props, getOptions } from '@eightshift/frontend-libs/scripts';
import { ModalOptions as ModalOptionsComponent } from '../../../components/modal/components/modal-options';
import manifest from './../manifest.json';


export const ModalOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Modal', 'delta9-digital-blocks')}>
				<ModalOptionsComponent
				{...props('modal', attributes, {
					setAttributes,
					options: getOptions(attributes, manifest),
				})}
			/>
		</PanelBody>
	);
};
