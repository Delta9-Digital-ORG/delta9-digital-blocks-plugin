import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconToggle,
	getOption,
	Control,
	NumberPicker,
	AnimatedContentVisibility,
	Section,
} from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const CarouselOptions = ({ attributes, setAttributes }) => {
	const carouselIsLoop = checkAttr('carouselIsLoop', attributes, manifest);
	const carouselShowItems = checkAttr('carouselShowItems', attributes, manifest);
	const carouselShowPrevNext = checkAttr('carouselShowPrevNext', attributes, manifest);
	const carouselShowPagination = checkAttr('carouselShowPagination', attributes, manifest);

	return (
		<PanelBody title={__('Carousel', 'delta9-digital-blocks-plugin')}>
			<Control className='es-h-between' icon={icons.itemLimit} label={__('Slides on screen', 'delta9-digital-blocks-plugin')} inlineLabel>
				<div className='es-h-end' >
					<AnimatedContentVisibility showIf={carouselShowItems > -1}>
						<NumberPicker
							{...getOption('carouselItemsToShow', attributes, manifest)}
							value={carouselShowItems}
							onChange={(value) => setAttributes({ [getAttrKey('carouselShowItems', attributes, manifest)]: value })}
							noBottomSpacing
							fixedWidth={2}
						/>
					</AnimatedContentVisibility>

					<Button
						icon={icons.automatic}
						label={__('Automatic', 'delta9-digital-blocks-plugin')}
						isPressed={carouselShowItems === -1}
						onClick={() => setAttributes({ [getAttrKey('carouselShowItems', attributes, manifest)]: carouselShowItems === -1 ? 1 : -1 })}
						className='es-is-v2-gutenberg-input-matched-button es-button-icon-24 es-button-square-32'
					/>
				</div>
			</Control>

			<Section icon={icons.options} label={__('Behavior & controls', 'delta9-digital-blocks-plugin')} additionalClasses='es-h-spaced' noBottomSpacing>
				<IconToggle
					icon={icons.loopMode}
					label={__('Loop', 'delta9-digital-blocks-plugin')}
					checked={carouselIsLoop}
					onChange={(value) => setAttributes({ [getAttrKey('carouselIsLoop', attributes, manifest)]: value })}
					type='tileButton'
				/>

				<IconToggle
					icon={icons.navigationButtons}
					label={__('Navigation', 'delta9-digital-blocks-plugin')}
					checked={carouselShowPrevNext}
					onChange={(value) => setAttributes({ [getAttrKey('carouselShowPrevNext', attributes, manifest)]: value })}
					type='tileButton'
				/>

				<IconToggle
					icon={icons.pagination}
					label={__('Pagination', 'delta9-digital-blocks-plugin')}
					checked={carouselShowPagination}
					onChange={(value) => setAttributes({ [getAttrKey('carouselShowPagination', attributes, manifest)]: value })}
					type='tileButton'
				/>
			</Section>
		</PanelBody>
	);
};
