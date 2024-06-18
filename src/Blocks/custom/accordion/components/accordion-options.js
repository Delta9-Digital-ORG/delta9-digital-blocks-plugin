import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconToggle } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const AccordionOptions = ({ attributes, setAttributes }) => {
	const accordionCloseAdjacent = checkAttr('accordionCloseAdjacent', attributes, manifest);

	return (
		<PanelBody title={__('Accordion', 'delta9-digital-blocks-plugin')}>
			<IconToggle
				icon={icons.autoClose}
				label={__('Close adjacent panels', 'delta9-digital-blocks-plugin')}
				help={__('when expanding a new one', 'delta9-digital-blocks-plugin')}
				checked={accordionCloseAdjacent}
				onChange={(value) => setAttributes({ [getAttrKey('accordionCloseAdjacent', attributes, manifest)]: value })}
				noBottomSpacing
				inlineHelp
			/>
		</PanelBody>
	);
};
