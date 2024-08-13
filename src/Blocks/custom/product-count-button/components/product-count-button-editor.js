import React, { useMemo } from 'react';
import { outputCssVariables, getUnique, props } from '@eightshift/frontend-libs/scripts';
import { ProductCountButtonEditor as ProductCountButtonEditorComponent } from '../../../components/product-count-button/components/product-count-button-editor';
import manifest from './../manifest.json';
import globalManifest from './../../../manifest.json';

export const ProductCountButtonEditor = ({ attributes, setAttributes }) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique, globalManifest)}

			<ProductCountButtonEditorComponent
				{...props('product-count-button', attributes, {
					setAttributes,
				})}
			/>
		</div>
	);
};
