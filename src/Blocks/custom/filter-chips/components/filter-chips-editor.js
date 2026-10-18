import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useBlockProps } from '@wordpress/block-editor';
import { checkAttr, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

// Preview of the chips the front end renders (non-empty terms, same order).
export const FilterChipsEditor = ({ attributes }) => {
	const { blockClass } = attributes;
	const taxonomy = checkAttr('filterChipsTaxonomy', attributes, manifest);
	const orderBy = checkAttr('filterChipsOrderBy', attributes, manifest);
	const allLabel = checkAttr('filterChipsAllLabel', attributes, manifest);
	const label = checkAttr('filterChipsLabel', attributes, manifest);

	const terms = useSelect((select) => select('core').getEntityRecords('taxonomy', taxonomy, {
		per_page: -1,
		hide_empty: true,
		orderby: orderBy === 'term_id' ? 'id' : orderBy,
		order: orderBy === 'count' ? 'desc' : 'asc',
		_fields: 'id,name',
	}), [taxonomy, orderBy]);

	const blockProps = useBlockProps({ className: classnames(blockClass) });

	return (
		<div {...blockProps}>
			{label && <span className={`${blockClass}__label`}>{label}</span>}
			<button type='button' className={`${blockClass}__chip`} aria-pressed='true'>{allLabel}</button>
			{(terms || []).map((term) => (
				<button type='button' key={term.id} className={`${blockClass}__chip`} aria-pressed='false'>{term.name}</button>
			))}
			{terms && !terms.length && <em>{__('No terms with posts in this taxonomy.', 'delta9-digital-blocks-plugin')}</em>}
		</div>
	);
};
