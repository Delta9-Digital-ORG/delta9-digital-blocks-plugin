import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const BadgeCardsOptions = ({ attributes, setAttributes }) => {
	const columns = checkAttr('badgeCardsColumns', attributes, manifest);
	const variant = checkAttr('badgeCardsVariant', attributes, manifest);
	const surface = checkAttr('badgeCardsSurface', attributes, manifest);
	const cycle = checkAttr('badgeCardsAccentCycle', attributes, manifest);

	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Badge cards', 'delta9-digital-blocks-plugin')}>
			<RangeControl
				label={__('Columns (desktop)', 'delta9-digital-blocks-plugin')}
				value={columns}
				onChange={(v) => setAttr('badgeCardsColumns', v)}
				min={manifest.options.badgeCardsColumns.min}
				max={manifest.options.badgeCardsColumns.max}
				step={manifest.options.badgeCardsColumns.step}
			/>
			<SelectControl
				label={__('Footer', 'delta9-digital-blocks-plugin')}
				value={variant}
				options={manifest.options.badgeCardsVariant}
				onChange={(v) => setAttr('badgeCardsVariant', v)}
			/>
			<SelectControl
				label={__('Placed on', 'delta9-digital-blocks-plugin')}
				value={surface}
				options={manifest.options.badgeCardsSurface}
				onChange={(v) => setAttr('badgeCardsSurface', v)}
			/>
			{[0, 1, 2, 3].map((i) => (
				<SelectControl
					key={i}
					label={`${__('Accent', 'delta9-digital-blocks-plugin')} ${i + 1}`}
					value={cycle[i]}
					options={manifest.options.accents}
					onChange={(v) => setAttr('badgeCardsAccentCycle', Object.assign([...cycle], { [i]: v }))}
				/>
			))}
		</PanelBody>
	);
};
