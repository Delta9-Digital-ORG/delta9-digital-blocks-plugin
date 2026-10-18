import React from 'react';
import { useBlockProps } from '@wordpress/block-editor';
import { checkAttr, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FilterSearchEditor = ({ attributes }) => {
	const { blockClass } = attributes;
	const placeholder = checkAttr('filterSearchPlaceholder', attributes, manifest);
	const blockProps = useBlockProps({ className: classnames(blockClass) });

	return (
		<div {...blockProps}>
			<input className={`${blockClass}__input`} type='search' placeholder={placeholder} disabled />
			<span className={`${blockClass}__submit`} />
		</div>
	);
};
