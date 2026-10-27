import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconToggle, getOption, Control, NumberPicker, AnimatedContentVisibility, Section, Select, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CarouselOptions = ({ attributes, setAttributes }) => {
	const carouselIsLoop = checkAttr('carouselIsLoop', attributes, manifest);
	const carouselShowItems = checkAttr('carouselShowItems', attributes, manifest);
	const carouselShowPrevNext = checkAttr('carouselShowPrevNext', attributes, manifest);
	const carouselShowPagination = checkAttr('carouselShowPagination', attributes, manifest);
	const carouselIsAutoplay = checkAttr('carouselIsAutoplay', attributes, manifest);
	const carouselEffect = checkAttr('carouselEffect', attributes, manifest);
	const carouselCenteredSlides = checkAttr('carouselCenteredSlides', attributes, manifest);
	const carouselSpaceBetween = checkAttr('carouselSpaceBetween', attributes, manifest);
	const carouselMobileItems = checkAttr('carouselMobileItems', attributes, manifest);
	const carouselAutoplayDelay = checkAttr('carouselAutoplayDelay', attributes, manifest);
	const carouselFill = checkAttr('carouselFill', attributes, manifest);

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
			
			<Section icon={icons.options} label={__('Effect', 'delta9-digital-blocks-plugin')} additionalClasses='es-h-spaced'>
				<Select
					value={carouselEffect}
					options={getOption('carouselEffect', attributes, manifest)}
					onChange={(value) => setAttributes({ [getAttrKey('carouselEffect', attributes, manifest)]: value })}
					label={__('Carousel Effect', 'international-student-theme')}
					additionalClasses={classnames('es-flex-grow-1', 'es-max-w-52')}
					noBottomSpacing
					simpleValue
					noSearch
				/>
			</Section>

			<Section icon={icons.options} label={__('Behavior & controls', 'delta9-digital-blocks-plugin')} additionalClasses='es-h-spaced' noBottomSpacing>
				<IconToggle
					icon={icons.loopMode}
					label={__('Loop', 'delta9-digital-blocks-plugin')}
					checked={carouselIsLoop}
					onChange={(value) => setAttributes({ [getAttrKey('carouselIsLoop', attributes, manifest)]: value })}
					type='tileButton'
				/>
				<IconToggle
					icon={icons.pagination}
					label={__('Autoplay', 'delta9-digital-blocks-plugin')}
					checked={carouselIsAutoplay}
					onChange={(value) => setAttributes({ [getAttrKey('carouselIsAutoplay', attributes, manifest)]: value })}
					type='tileButton'
				/>
			</Section>

			<AnimatedContentVisibility showIf={carouselIsAutoplay}>
				<Control icon={icons.clock} label={__('Autoplay delay (ms)', 'delta9-digital-blocks-plugin')} inlineLabel>
					<NumberPicker
						{...getOption('carouselAutoplayDelay', attributes, manifest)}
						value={carouselAutoplayDelay}
						onChange={(value) => setAttributes({ [getAttrKey('carouselAutoplayDelay', attributes, manifest)]: value })}
						noBottomSpacing
						fixedWidth={5}
					/>
				</Control>
			</AnimatedContentVisibility>

			<Section icon={icons.options} label={__('Layout', 'delta9-digital-blocks-plugin')}>
				<IconToggle
					icon={icons.expandXl}
					label={__('Fill the container', 'delta9-digital-blocks-plugin')}
					help={__('Covers the parent box (e.g. a photo frame with a height). Images crop to fill; dots and arrows sit on top. Front end only.', 'delta9-digital-blocks-plugin')}
					checked={carouselFill}
					onChange={(value) => setAttributes({ [getAttrKey('carouselFill', attributes, manifest)]: value })}
				/>
				<IconToggle
					icon={icons.horizontalAlignCenter}
					label={__('Center the active slide', 'delta9-digital-blocks-plugin')}
					checked={carouselCenteredSlides}
					onChange={(value) => setAttributes({ [getAttrKey('carouselCenteredSlides', attributes, manifest)]: value })}
				/>
				<Control icon={icons.containerSpacingH} label={__('Space between slides (px)', 'delta9-digital-blocks-plugin')} inlineLabel>
					<NumberPicker
						{...getOption('carouselSpaceBetween', attributes, manifest)}
						value={carouselSpaceBetween}
						onChange={(value) => setAttributes({ [getAttrKey('carouselSpaceBetween', attributes, manifest)]: value })}
						noBottomSpacing
						fixedWidth={3}
					/>
				</Control>
				<Control icon={icons.itemLimit} label={__('Slides on mobile (0 = same as above)', 'delta9-digital-blocks-plugin')} inlineLabel noBottomSpacing>
					<NumberPicker
						{...getOption('carouselMobileItems', attributes, manifest)}
						value={carouselMobileItems}
						onChange={(value) => setAttributes({ [getAttrKey('carouselMobileItems', attributes, manifest)]: value })}
						noBottomSpacing
						fixedWidth={3}
					/>
				</Control>
			</Section>

			<Section icon={icons.options} label={__('Navigation', 'delta9-digital-blocks-plugin')} additionalClasses='es-h-spaced'>
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
