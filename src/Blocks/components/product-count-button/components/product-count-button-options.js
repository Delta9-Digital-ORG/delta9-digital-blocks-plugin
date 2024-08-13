import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	UseToggle,
	LinkInput,
	OptionSelector,
	props,
	getOptions,
	Section,
	generateUseToggleConfig,
	ColorPicker,
} from '@eightshift/frontend-libs/scripts';
import { IconOptions } from '../../icon/components/icon-options';
import manifest from './../manifest.json';

export const ProductCountButtonOptions = (attributes) => {
	const {
		setAttributes,

		hideAriaLabel = false,
		hideId = false,
		hideUrl = false,
		hideVariantPicker = false,
		hideColorPicker = false,

		additionalControls,
	} = attributes;

	const productCountButtonId = checkAttr('productCountButtonId', attributes, manifest);
	const productCountButtonAriaLabel = checkAttr('productCountButtonAriaLabel', attributes, manifest);
	const productCountButtonUrl = checkAttr('productCountButtonUrl', attributes, manifest);
	const productCountButtonIsNewTab = checkAttr('productCountButtonIsNewTab', attributes, manifest);
	const productCountButtonVariant = checkAttr('productCountButtonVariant', attributes, manifest);
	const productCountButtonColor = checkAttr('productCountButtonColor', attributes, manifest);

	return (
		<UseToggle {...generateUseToggleConfig(attributes, manifest, 'productCountButtonUse')}>
			{!hideUrl &&
				<LinkInput
					url={productCountButtonUrl}
					opensInNewTab={productCountButtonIsNewTab}
					onChange={({ url, newTab, isAnchor }) => setAttributes({
						[getAttrKey('productCountButtonUrl', attributes, manifest)]: url,
						[getAttrKey('productCountButtonIsNewTab', attributes, manifest)]: newTab,
						[getAttrKey('productCountButtonIsAnchor', attributes, manifest)]: isAnchor ?? false,
					})}
				/>
			}

			{!hideVariantPicker &&
				<OptionSelector
					icon={icons.genericShapesAlt}
					label={__('Style', 'delta9-digital-blocks-plugin')}
					value={productCountButtonVariant}
					onChange={(value) => setAttributes({ [getAttrKey('productCountButtonVariant', attributes, manifest)]: value })}
					options={getOption('productCountButtonVariant', attributes, manifest)}
					reducedBottomSpacing={!hideColorPicker}
					inlineLabel
					iconOnly
				/>
			}

			{!hideColorPicker &&
				<ColorPicker
					icon={icons.colorAlt}
					label={__('Color', 'delta9-digital-blocks-plugin')}
					value={productCountButtonColor}
					onChange={(value) => setAttributes({ [getAttrKey('productCountButtonColor', attributes, manifest)]: value })}
					options={getOption('productCountButtonColor', attributes, manifest)}
					colors={getOption('productCountButtonColor', attributes, manifest, true)}
					tooltip={__('Color', 'delta9-digital-blocks-plugin')}
					inlineLabel
					expanded
					border
				/>
			}

			{additionalControls}

			<IconOptions
				{...props('icon', attributes, {
					options: getOptions(attributes, manifest),
				})}
				noExpandButton
				hideSizePicker
			/>

			<Section showIf={!hideAriaLabel} icon={icons.a11y} label={__('Accessibility', 'delta9-digital-blocks-plugin')} collapsable reducedBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.ariaLabel} label={__('ARIA label', 'delta9-digital-blocks-plugin')} />}
					value={productCountButtonAriaLabel}
					onChange={(value) => setAttributes({ [getAttrKey('productCountButtonAriaLabel', attributes, manifest)]: value })}
					help={__('Description of the Product Count Button.', 'delta9-digital-blocks-plugin')}
				/>
			</Section>

			<Section showIf={!hideId} icon={icons.tools} label={__('Advanced', 'delta9-digital-blocks-plugin')} collapsable noBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.id} label={__('Unique identifier', 'delta9-digital-blocks-plugin')} />}
					value={productCountButtonId}
					onChange={(value) => setAttributes({ [getAttrKey('productCountButtonId', attributes, manifest)]: value })}
				/>
			</Section>
		</UseToggle>
	);
};
