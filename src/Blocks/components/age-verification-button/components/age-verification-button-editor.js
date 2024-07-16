import React, { useMemo } from 'react';
import { outputCssVariables, getUnique, props, selector, classnames } from '@eightshift/frontend-libs/scripts';
import { ButtonEditor } from '../../../components/button/components/button-editor';
import manifest from './../manifest.json';

export const AgeVerificationButtonEditor = (attributes) => {
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

	const ageVerificationButtonClass = classnames(
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	);

	return (
		<button className={ageVerificationButtonClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique)}

			<ButtonEditor
				{...props('button', attributes, {
					setAttributes,
				})}
			/>
		</button>
	);
};
