import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ScrollStoryOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Scroll story', 'delta9-digital-blocks-plugin')}>
			<SelectControl
				label={__('Panel side', 'delta9-digital-blocks-plugin')}
				value={checkAttr('scrollStoryPanelSide', attributes, manifest)}
				options={[
					{ label: __('Start (left)', 'delta9-digital-blocks-plugin'), value: 'start' },
					{ label: __('End (right)', 'delta9-digital-blocks-plugin'), value: 'end' },
				]}
				onChange={(value) => setAttributes({ [getAttrKey('scrollStoryPanelSide', attributes, manifest)]: value })}
			/>
			<RangeControl
				label={__('Trigger line (% of screen height)', 'delta9-digital-blocks-plugin')}
				help={__('A step becomes active when its top passes this line.', 'delta9-digital-blocks-plugin')}
				value={Math.round(checkAttr('scrollStoryTrigger', attributes, manifest) * 100)}
				min={20}
				max={90}
				onChange={(value) => setAttributes({ [getAttrKey('scrollStoryTrigger', attributes, manifest)]: value / 100 })}
			/>
		</PanelBody>
	);
};
