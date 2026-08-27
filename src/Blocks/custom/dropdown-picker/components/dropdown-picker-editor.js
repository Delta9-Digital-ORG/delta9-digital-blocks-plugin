import React, { useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import { outputCssVariables, getUnique } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';
import globalManifest from './../../../manifest.json';

export const DropdownPickerEditor = ({ attributes }) => {
	const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique, globalManifest)}

			<div className={`${blockClass}__placeholder`}>
				<select disabled className={`${blockClass}__select`}>
					<option>{__('Pack size dropdown', 'delta9-digital-blocks-plugin')}</option>
				</select>
				<p className={`${blockClass}__hint`}>
					{__('Configure pack options in the product settings.', 'delta9-digital-blocks-plugin')}
				</p>
			</div>
		</div>
	);
};
