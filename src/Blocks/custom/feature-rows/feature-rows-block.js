import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { D9RepeaterControl, D9_ACCENTS } from '../../assets/scripts/d9-repeater-control';
import manifest from './manifest.json';

const ROW_FIELDS = [
	{ key: 'title', label: __('Title', 'delta9-digital-blocks-plugin') },
	{ key: 'body', label: __('Text', 'delta9-digital-blocks-plugin'), type: 'textarea' },
];

export const FeatureRows = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const attr = (key) => checkAttr(key, attributes, manifest);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });
	const imageId = attr('featureRowsImageId');

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Feature rows', 'delta9-digital-blocks-plugin')}>
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={['image']}
							value={imageId}
							onSelect={(media) => setAttributes({
								[getAttrKey('featureRowsImageId', attributes, manifest)]: media.id,
								[getAttrKey('featureRowsImageUrl', attributes, manifest)]: media.url,
								[getAttrKey('featureRowsImageAlt', attributes, manifest)]: media.alt || '',
							})}
							render={({ open }) => (
								<div style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
									<Button variant='secondary' onClick={open}>{imageId ? __('Replace image', 'delta9-digital-blocks-plugin') : __('Choose image', 'delta9-digital-blocks-plugin')}</Button>
									{imageId > 0 && (
										<Button variant='link' isDestructive onClick={() => setAttributes({ [getAttrKey('featureRowsImageId', attributes, manifest)]: 0, [getAttrKey('featureRowsImageUrl', attributes, manifest)]: '' })}>
											{__('Remove', 'delta9-digital-blocks-plugin')}
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
					<TextControl label={__('Image alt text', 'delta9-digital-blocks-plugin')} value={attr('featureRowsImageAlt')} onChange={(v) => setAttr('featureRowsImageAlt', v)} />
					<SelectControl label={__('Image side', 'delta9-digital-blocks-plugin')} value={attr('featureRowsImageSide')} options={manifest.options.featureRowsImageSide} onChange={(v) => setAttr('featureRowsImageSide', v)} />
					<SelectControl label={__('Accent', 'delta9-digital-blocks-plugin')} value={attr('featureRowsAccent')} options={D9_ACCENTS} onChange={(v) => setAttr('featureRowsAccent', v)} />
					<TextControl label={__('Pill eyebrow', 'delta9-digital-blocks-plugin')} help='01 · Brand Identity' value={attr('featureRowsEyebrow')} onChange={(v) => setAttr('featureRowsEyebrow', v)} />
					<TextControl label={__('Heading', 'delta9-digital-blocks-plugin')} value={attr('featureRowsHeading')} onChange={(v) => setAttr('featureRowsHeading', v)} />
				</PanelBody>
				<PanelBody title={__('Rows', 'delta9-digital-blocks-plugin')}>
					<D9RepeaterControl value={attr('featureRowsRows')} onChange={(v) => setAttr('featureRowsRows', v)} fields={ROW_FIELDS} itemLabel={__('Row', 'delta9-digital-blocks-plugin')} />
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, featureRowsServerSideRender: true }} />
		</>
	);
};
