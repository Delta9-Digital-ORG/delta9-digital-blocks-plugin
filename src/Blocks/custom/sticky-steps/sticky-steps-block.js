import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { D9RepeaterControl, D9_ROW_FIELDS } from '../../assets/scripts/d9-repeater-control';
import manifest from './manifest.json';

export const StickySteps = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const attr = (key) => checkAttr(key, attributes, manifest);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });
	const text = (key, label, help) => <TextControl label={label} help={help} value={attr(key)} onChange={(v) => setAttr(key, v)} />;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Intro', 'delta9-digital-blocks-plugin')}>
					{text('stickyStepsEyebrow', __('Pill above the heading', 'delta9-digital-blocks-plugin'), __('Optional, e.g. SEO', 'delta9-digital-blocks-plugin'))}
					{text('stickyStepsHeading', __('Heading', 'delta9-digital-blocks-plugin'))}
					<TextareaControl label={__('Lead', 'delta9-digital-blocks-plugin')} value={attr('stickyStepsLead')} onChange={(v) => setAttr('stickyStepsLead', v)} />
					{text('stickyStepsCtaLabel', __('Button label', 'delta9-digital-blocks-plugin'))}
					{text('stickyStepsCtaHref', __('Button URL', 'delta9-digital-blocks-plugin'))}
					<SelectControl label={__('Button style', 'delta9-digital-blocks-plugin')} value={attr('stickyStepsCtaStyle')} options={manifest.options.stickyStepsCtaStyle} onChange={(v) => setAttr('stickyStepsCtaStyle', v)} />
				</PanelBody>
				<PanelBody title={__('Layout', 'delta9-digital-blocks-plugin')} initialOpen={false}>
					<SelectControl label={__('Columns', 'delta9-digital-blocks-plugin')} value={attr('stickyStepsColumnRatio')} options={manifest.options.stickyStepsColumnRatio} onChange={(v) => setAttr('stickyStepsColumnRatio', v)} />
					<RangeControl label={__('Pin offset (px)', 'delta9-digital-blocks-plugin')} min={0} max={300} value={attr('stickyStepsStickyTop')} onChange={(v) => setAttr('stickyStepsStickyTop', v)} />
					<SelectControl label={__('Row size', 'delta9-digital-blocks-plugin')} value={attr('stickyStepsRowsSize')} options={manifest.options.stickyStepsRowsSize} onChange={(v) => setAttr('stickyStepsRowsSize', v)} />
					{text('stickyStepsRowsEyebrow', __('Label above the rows', 'delta9-digital-blocks-plugin'), __('Optional, e.g. Our expertise', 'delta9-digital-blocks-plugin'))}
				</PanelBody>
				<PanelBody title={__('Rows', 'delta9-digital-blocks-plugin')}>
					<D9RepeaterControl value={attr('stickyStepsRows')} onChange={(v) => setAttr('stickyStepsRows', v)} fields={D9_ROW_FIELDS} itemLabel={__('Row', 'delta9-digital-blocks-plugin')} />
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, stickyStepsServerSideRender: true }} />
		</>
	);
};
