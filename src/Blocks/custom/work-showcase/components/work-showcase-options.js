import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, RangeControl, ToggleControl, ColorPalette, BaseControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { WorkShowcaseItems } from './work-showcase-items';

export const WorkShowcaseOptions = ({ attributes, setAttributes }) => {
	const primaryLabel = checkAttr('workShowcasePrimaryCtaLabel', attributes, manifest);
	const primaryUrl = checkAttr('workShowcasePrimaryCtaUrl', attributes, manifest);
	const secondaryLabel = checkAttr('workShowcaseSecondaryCtaLabel', attributes, manifest);
	const secondaryUrl = checkAttr('workShowcaseSecondaryCtaUrl', attributes, manifest);

	const background = checkAttr('workShowcaseBackground', attributes, manifest);
	const tileRadius = checkAttr('workShowcaseTileRadius', attributes, manifest);
	const minHeight = checkAttr('workShowcaseMinHeight', attributes, manifest);
	const parallax = checkAttr('workShowcaseParallax', attributes, manifest);
	const scrollExit = checkAttr('workShowcaseScrollExit', attributes, manifest);
	const autoReplay = checkAttr('workShowcaseAutoReplay', attributes, manifest);

	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<>
			<PanelBody title={__('Content', 'delta9-digital-blocks-plugin')}>
				<TextControl
					label={__('Primary CTA label', 'delta9-digital-blocks-plugin')}
					value={primaryLabel}
					onChange={(v) => setAttr('workShowcasePrimaryCtaLabel', v)}
				/>
				<TextControl
					label={__('Primary CTA URL', 'delta9-digital-blocks-plugin')}
					type="url"
					value={primaryUrl}
					onChange={(v) => setAttr('workShowcasePrimaryCtaUrl', v)}
				/>
				<TextControl
					label={__('Secondary CTA label', 'delta9-digital-blocks-plugin')}
					value={secondaryLabel}
					onChange={(v) => setAttr('workShowcaseSecondaryCtaLabel', v)}
				/>
				<TextControl
					label={__('Secondary CTA URL', 'delta9-digital-blocks-plugin')}
					type="url"
					value={secondaryUrl}
					onChange={(v) => setAttr('workShowcaseSecondaryCtaUrl', v)}
				/>
			</PanelBody>

			<PanelBody title={__('Tiles', 'delta9-digital-blocks-plugin')} initialOpen={false}>
				<WorkShowcaseItems attributes={attributes} setAttributes={setAttributes} />
			</PanelBody>

			<PanelBody title={__('Display', 'delta9-digital-blocks-plugin')} initialOpen={false}>
				<BaseControl label={__('Background', 'delta9-digital-blocks-plugin')} __nextHasNoMarginBottom>
					<ColorPalette
						value={background}
						onChange={(v) => setAttr('workShowcaseBackground', v || '#0b0b0f')}
						enableAlpha={false}
					/>
				</BaseControl>

				<RangeControl
					label={__('Minimum height (vh)', 'delta9-digital-blocks-plugin')}
					value={minHeight}
					onChange={(v) => setAttr('workShowcaseMinHeight', v)}
					min={manifest.options.workShowcaseMinHeight.min}
					max={manifest.options.workShowcaseMinHeight.max}
					step={manifest.options.workShowcaseMinHeight.step}
				/>

				<RangeControl
					label={__('Corner radius', 'delta9-digital-blocks-plugin')}
					value={tileRadius}
					onChange={(v) => setAttr('workShowcaseTileRadius', v)}
					min={manifest.options.workShowcaseTileRadius.min}
					max={manifest.options.workShowcaseTileRadius.max}
					step={manifest.options.workShowcaseTileRadius.step}
				/>

				<ToggleControl
					label={__('Pointer parallax', 'delta9-digital-blocks-plugin')}
					checked={parallax}
					onChange={(v) => setAttr('workShowcaseParallax', v)}
				/>
				<ToggleControl
					label={__('Scroll exit', 'delta9-digital-blocks-plugin')}
					checked={scrollExit}
					onChange={(v) => setAttr('workShowcaseScrollExit', v)}
				/>
				<ToggleControl
					label={__('Show replay button', 'delta9-digital-blocks-plugin')}
					checked={autoReplay}
					onChange={(v) => setAttr('workShowcaseAutoReplay', v)}
				/>
			</PanelBody>
		</>
	);
};
