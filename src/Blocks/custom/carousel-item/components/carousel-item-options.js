import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { ImageOptions } from '../../../components/image/components/image-options';

export const CarouselItemOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Carousel item', 'delta9-digital-blocks-plugin')}>
			<ImageOptions
				{...props('image', attributes, { setAttributes })}
				noLabel
				noUseToggle
				noExpandButton
				noFullSizeToggle
			/>
		</PanelBody>
	);
};
