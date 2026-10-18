import React, { useMemo } from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { outputCssVariables, getUnique, props, checkAttr, classnames, selector } from '@eightshift/frontend-libs/scripts';
import { ImageEditor } from '../../../components/image/components/image-editor';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const CarouselItemEditor = ({ attributes, setAttributes }) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
	} = attributes;

	const carouselItemUseContent = checkAttr('carouselItemUseContent', attributes, manifest);

	return (
		<div className={classnames(blockClass, carouselItemUseContent && selector(blockClass, blockClass, '', 'content'))} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique, globalManifest)}

			{carouselItemUseContent ? (
				<InnerBlocks />
			) : (
				<ImageEditor
					{...props('image', attributes, { setAttributes })}
				/>
			)}
		</div>
	);
};
