import React, { useMemo } from 'react';
import { outputCssVariables, getUnique, props, selector, classnames } from '@eightshift/frontend-libs/scripts';
import { ButtonEditor } from '../../../components/button/components/button-editor';
import manifest from './../manifest.json';

export const ModalButtonEditor = (attributes) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		componentClass,
	} = manifest;

	const {
		setAttributes,
		selectorClass = componentClass,
		additionalClass,
		blockClass,
	} = attributes;

	const modalButtonClass = classnames(
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	);

	return (
		<button className={modalButtonClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique)}

			<ButtonEditor
				{...props('button', attributes, {
					setAttributes,
				})}
			/>
		</button>
	);
};
