import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl, Spinner } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const InsightCardsOptions = ({ attributes, setAttributes }) => {
	const query = checkAttr('insightCardsQuery', attributes, manifest);
	const featuredId = checkAttr('insightCardsFeaturedId', attributes, manifest);
	const ids = checkAttr('insightCardsIds', attributes, manifest);
	const category = checkAttr('insightCardsCategory', attributes, manifest);

	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	const { posts, categories } = useSelect((select) => ({
		posts: select('core').getEntityRecords('postType', 'post', { per_page: 50, status: 'publish', _fields: 'id,title' }),
		categories: select('core').getEntityRecords('taxonomy', 'category', { per_page: 100, _fields: 'id,name' }),
	}), []);

	if (!posts || !categories) {
		return <PanelBody title={__('Insights', 'delta9-digital-blocks-plugin')}><Spinner /></PanelBody>;
	}

	const postOptions = posts.map((p) => ({ label: p.title?.rendered || `#${p.id}`, value: p.id }));
	const none = (label) => [{ label, value: 0 }];

	return (
		<PanelBody title={__('Insights', 'delta9-digital-blocks-plugin')}>
			<SelectControl
				label={__('Featured post', 'delta9-digital-blocks-plugin')}
				help={__('Default: the sticky post, else the newest.', 'delta9-digital-blocks-plugin')}
				value={featuredId}
				options={[...none(__('Automatic', 'delta9-digital-blocks-plugin')), ...postOptions]}
				onChange={(v) => setAttr('insightCardsFeaturedId', parseInt(v, 10) || 0)}
			/>
			<SelectControl
				label={__('Other two posts', 'delta9-digital-blocks-plugin')}
				value={query}
				options={manifest.options.insightCardsQuery}
				onChange={(v) => setAttr('insightCardsQuery', v)}
			/>
			{query === 'manual' ? [0, 1].map((i) => (
				<SelectControl
					key={i}
					label={`${__('Post', 'delta9-digital-blocks-plugin')} ${i + 1}`}
					value={ids[i] ?? 0}
					options={[...none(__('Select a post', 'delta9-digital-blocks-plugin')), ...postOptions]}
					onChange={(v) => setAttr('insightCardsIds', Object.assign([...ids], { [i]: parseInt(v, 10) || 0 }).filter((id, n) => id || n < 2))}
				/>
			)) : (
				<SelectControl
					label={__('Limit to category', 'delta9-digital-blocks-plugin')}
					value={category}
					options={[...none(__('All categories', 'delta9-digital-blocks-plugin')), ...categories.map((c) => ({ label: c.name, value: c.id }))]}
					onChange={(v) => setAttr('insightCardsCategory', parseInt(v, 10) || 0)}
				/>
			)}
			<TextControl
				label={__('Featured pill label', 'delta9-digital-blocks-plugin')}
				value={checkAttr('insightCardsFeaturedLabel', attributes, manifest)}
				onChange={(v) => setAttr('insightCardsFeaturedLabel', v)}
			/>
		</PanelBody>
	);
};
