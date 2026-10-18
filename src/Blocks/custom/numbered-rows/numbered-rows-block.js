import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { D9RepeaterControl, D9_ACCENTS, D9_ROW_FIELDS } from '../../assets/scripts/d9-repeater-control';
import manifest from './manifest.json';

export const NumberedRows = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const attr = (key) => checkAttr(key, attributes, manifest);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Numbered rows', 'delta9-digital-blocks-plugin')}>
					<SelectControl label={__('Size', 'delta9-digital-blocks-plugin')} value={attr('numberedRowsSize')} options={manifest.options.numberedRowsSize} onChange={(v) => setAttr('numberedRowsSize', v)} />
					<RangeControl label={__('Columns', 'delta9-digital-blocks-plugin')} min={1} max={2} value={attr('numberedRowsColumns')} onChange={(v) => setAttr('numberedRowsColumns', v)} />
					<SelectControl
						label={__('One accent for every number', 'delta9-digital-blocks-plugin')}
						value={attr('numberedRowsAccent')}
						options={[{ label: __('No, rotate', 'delta9-digital-blocks-plugin'), value: '' }, ...D9_ACCENTS]}
						onChange={(v) => setAttr('numberedRowsAccent', v)}
					/>
					<TextControl
						label={__('Accent rotation', 'delta9-digital-blocks-plugin')}
						help={__('Comma separated. Two accents with two columns colour one column each.', 'delta9-digital-blocks-plugin')}
						value={(attr('numberedRowsAccents') || []).join(', ')}
						onChange={(v) => setAttr('numberedRowsAccents', v.split(',').map((s) => s.trim()).filter(Boolean))}
					/>
					<ToggleControl label={__('Closing hairline', 'delta9-digital-blocks-plugin')} checked={attr('numberedRowsHairlineBottom')} onChange={(v) => setAttr('numberedRowsHairlineBottom', v)} />
				</PanelBody>
				<PanelBody title={__('Rows', 'delta9-digital-blocks-plugin')}>
					<D9RepeaterControl value={attr('numberedRowsRows')} onChange={(v) => setAttr('numberedRowsRows', v)} fields={D9_ROW_FIELDS} itemLabel={__('Row', 'delta9-digital-blocks-plugin')} />
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, numberedRowsServerSideRender: true }} />
		</>
	);
};
