import React, { useMemo } from 'react';
import { outputCssVariables, getUnique, props } from '@eightshift/frontend-libs/scripts';
import { ProductCountEditor as ProductCountEditorComponent } from '../../../components/product-count/components/product-count-editor';
import manifest from './../manifest.json';
import globalManifest from './../../../manifest.json';

export const ProductCountEditor = ({ attributes, setAttributes }) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique, globalManifest)}

			<ProductCountEditorComponent />
		</div>
	);
};
