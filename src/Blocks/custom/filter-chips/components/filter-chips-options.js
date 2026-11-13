import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FilterChipsOptions = ({ attributes, setAttributes }) => {
	const taxonomies = useSelect((select) => select('core').getTaxonomies({ per_page: -1 }), []);
	const set = (key) => (value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Filter chips', 'delta9-digital-blocks-plugin')}>
			<TextControl
				label={__('Filter group', 'delta9-digital-blocks-plugin')}
				help={__('Must match the Filter grid it controls.', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsGroup', attributes, manifest)}
				onChange={set('filterChipsGroup')}
			/>
			<SelectControl
				label={__('Taxonomy', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsTaxonomy', attributes, manifest)}
				options={(taxonomies || []).filter((tax) => tax.visibility?.show_ui).map((tax) => ({ label: tax.name, value: tax.slug }))}
				onChange={set('filterChipsTaxonomy')}
			/>
			<SelectControl
				label={__('Order', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsOrderBy', attributes, manifest)}
				options={[
					{ label: __('Name', 'delta9-digital-blocks-plugin'), value: 'name' },
					{ label: __('Created (term ID)', 'delta9-digital-blocks-plugin'), value: 'term_id' },
					{ label: __('Most posts first', 'delta9-digital-blocks-plugin'), value: 'count' },
				]}
				onChange={set('filterChipsOrderBy')}
			/>
			<TextControl
				label={__('"All" label', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsAllLabel', attributes, manifest)}
				onChange={set('filterChipsAllLabel')}
			/>
			<TextControl
				label={__('Label (optional)', 'delta9-digital-blocks-plugin')}
				help={__('Shown before the chips, e.g. "Regional route".', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsLabel', attributes, manifest)}
				onChange={set('filterChipsLabel')}
			/>
			<TextControl
				label={__('URL parameter (optional)', 'delta9-digital-blocks-plugin')}
				help={__('Keeps the selection in the URL, e.g. "route" for ?route=northeast.', 'delta9-digital-blocks-plugin')}
				value={checkAttr('filterChipsUrlParam', attributes, manifest)}
				onChange={set('filterChipsUrlParam')}
			/>
		</PanelBody>
	);
};
