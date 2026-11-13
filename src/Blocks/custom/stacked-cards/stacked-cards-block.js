import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { D9RepeaterControl } from '../../assets/scripts/d9-repeater-control';
import manifest from './manifest.json';

const CARD_FIELDS = [
	{ key: 'title', label: __('Title', 'delta9-digital-blocks-plugin') },
	{ key: 'badge', label: __('Badge', 'delta9-digital-blocks-plugin'), help: __('Defaults to the card number (01).', 'delta9-digital-blocks-plugin') },
	{ key: 'id', label: __('Anchor', 'delta9-digital-blocks-plugin'), help: __('Jump link target, e.g. brand-1.', 'delta9-digital-blocks-plugin') },
	{ key: 'accent', label: __('Accent', 'delta9-digital-blocks-plugin'), type: 'accent' },
	{ key: 'ctaLabel', label: __('Button label', 'delta9-digital-blocks-plugin') },
	{ key: 'ctaHref', label: __('Button URL', 'delta9-digital-blocks-plugin') },
	{ key: 'body', label: __('Text', 'delta9-digital-blocks-plugin'), type: 'textarea' },
	{ key: 'tags', label: __('Tags (comma separated)', 'delta9-digital-blocks-plugin') },
];

export const StackedCards = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const attr = (key) => checkAttr(key, attributes, manifest);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });
	// Tags are stored as arrays; edit them as text.
	const cards = (attr('stackedCardsCards') || []).map((c) => ({ ...c, tags: Array.isArray(c.tags) ? c.tags.join(', ') : (c.tags || '') }));

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Stacked cards', 'delta9-digital-blocks-plugin')}>
					<TextControl label={__('Heading', 'delta9-digital-blocks-plugin')} value={attr('stackedCardsHeading')} onChange={(v) => setAttr('stackedCardsHeading', v)} />
					<TextareaControl label={__('Lead', 'delta9-digital-blocks-plugin')} value={attr('stackedCardsLead')} onChange={(v) => setAttr('stackedCardsLead', v)} />
					<ToggleControl label={__('Jump links', 'delta9-digital-blocks-plugin')} checked={attr('stackedCardsShowJumpLinks')} onChange={(v) => setAttr('stackedCardsShowJumpLinks', v)} />
					<RangeControl label={__('Pin offset (px)', 'delta9-digital-blocks-plugin')} min={0} max={300} value={attr('stackedCardsPinTop')} onChange={(v) => setAttr('stackedCardsPinTop', v)} />
				</PanelBody>
				<PanelBody title={__('Cards', 'delta9-digital-blocks-plugin')}>
					<D9RepeaterControl
						value={cards}
						onChange={(v) => setAttr('stackedCardsCards', v.map((c) => ({ ...c, tags: String(c.tags || '').split(',').map((s) => s.trim()).filter(Boolean) })))}
						fields={CARD_FIELDS}
						itemLabel={__('Card', 'delta9-digital-blocks-plugin')}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, stackedCardsServerSideRender: true }} />
		</>
	);
};
