import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props, checkAttr, getAttrKey, icons, IconToggle } from '@eightshift/frontend-libs/scripts';
import { ImageOptions } from '../../../components/image/components/image-options';
import manifest from '../manifest.json';

export const CarouselItemOptions = ({ attributes, setAttributes }) => {
	const carouselItemUseContent = checkAttr('carouselItemUseContent', attributes, manifest);

	return (
		<PanelBody title={__('Carousel item', 'delta9-digital-blocks-plugin')}>
			<IconToggle
				icon={icons.containerSpacing}
				label={__('Content slide (blocks instead of an image)', 'delta9-digital-blocks-plugin')}
				checked={carouselItemUseContent}
				onChange={(value) => setAttributes({ [getAttrKey('carouselItemUseContent', attributes, manifest)]: value })}
			/>

			{!carouselItemUseContent && (
				<ImageOptions
					{...props('image', attributes, { setAttributes })}
					noLabel
					noUseToggle
					noExpandButton
					noFullSizeToggle
				/>
			)}
		</PanelBody>
	);
};
