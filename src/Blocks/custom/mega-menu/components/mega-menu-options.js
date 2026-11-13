import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const MegaMenuOptions = ({ attributes, setAttributes }) => {
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });
	const promo = checkAttr('megaMenuPromo', attributes, manifest);

	const work = useSelect((select) => select('core').getEntityRecords('postType', 'work', { per_page: 100, status: 'publish', _fields: 'id,title' }), []);

	return (
		<>
			<PanelBody title={__('Triggers', 'delta9-digital-blocks-plugin')}>
				<p>{__('Each panel attaches to the navigation item that links to this URL.', 'delta9-digital-blocks-plugin')}</p>
				{[
					['megaMenuServicesTrigger', __('Services', 'delta9-digital-blocks-plugin')],
					['megaMenuWorkTrigger', __('Work', 'delta9-digital-blocks-plugin')],
					['megaMenuIntegrationsTrigger', __('Integrations', 'delta9-digital-blocks-plugin')],
				].map(([key, label]) => (
					<TextControl key={key} label={label} value={checkAttr(key, attributes, manifest)} onChange={(v) => setAttr(key, v)} />
				))}
			</PanelBody>
			<PanelBody title={__('Services promo', 'delta9-digital-blocks-plugin')} initialOpen={false}>
				{['eyebrow', 'title', 'text', 'label', 'url'].map((key) => (
					<TextControl
						key={key}
						label={key}
						value={promo?.[key] ?? ''}
						onChange={(v) => setAttr('megaMenuPromo', { ...promo, [key]: v })}
					/>
				))}
			</PanelBody>
			<PanelBody title={__('Work', 'delta9-digital-blocks-plugin')} initialOpen={false}>
				<SelectControl
					label={__('Featured case study', 'delta9-digital-blocks-plugin')}
					help={__('Automatic: the newest work post with stats.', 'delta9-digital-blocks-plugin')}
					value={checkAttr('megaMenuFeaturedWorkId', attributes, manifest)}
					options={[
						{ label: __('Automatic', 'delta9-digital-blocks-plugin'), value: 0 },
						...(work ?? []).map((p) => ({ label: p.title?.rendered || `#${p.id}`, value: p.id })),
					]}
					onChange={(v) => setAttr('megaMenuFeaturedWorkId', parseInt(v, 10) || 0)}
				/>
			</PanelBody>
		</>
	);
};
