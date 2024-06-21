import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs/scripts';
import { ImageOptions } from '../../../components/image/components/image-options';
import { SocialNetworksOptions } from '../../../components/social-networks/components/social-networks-options';
import { CopyrightOptions } from '../../../components/copyright/components/copyright-options';

export const SiteFooterOptions = ({ attributes, setAttributes }) => {
	return (
		<PanelBody title={__('Site navigation', 'delta9-digital-blocks-plugin')}>
			<ImageOptions
				{...props('logo', attributes, { setAttributes })}
				label={__('Logo', 'delta9-digital-blocks-plugin')}
				hideRoundedCornersToggle
				hideFullSizeToggle
				reducedBottomSpacing
			/>

			<CopyrightOptions
				{...props('copyright', attributes, { setAttributes })}
				label={__('Copyright', 'delta9-digital-blocks-plugin')}
				reducedBottomSpacing
			/>

			<SocialNetworksOptions
				{...props('socialNetworks', attributes, { setAttributes })}
				hideModeSelector
				noBottomSpacing
			/>
		</PanelBody>
	);
};
