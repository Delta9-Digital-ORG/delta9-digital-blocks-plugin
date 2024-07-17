import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, NumberPicker, Toggle } from '@eightshift/frontend-libs/scripts';
import { TextControl } from '@wordpress/components';
import manifest from './../manifest.json';

export const AgeVerificationOptions = (attributes) => {
	const {
		title: manifestTitle,
	} = manifest;

	const {
		setAttributes,
		label = manifestTitle,
		showLabel = false,
	} = attributes;

	const ageVerificationUse = checkAttr('ageVerificationUse', attributes, manifest);
	const ageVerificationConfirmText = checkAttr('ageVerificationConfirmText', attributes, manifest);
	const ageVerificationDeclineText = checkAttr('ageVerificationDeclineText', attributes, manifest);
	const ageVerificationDeclineLink = checkAttr('ageVerificationDeclineLink', attributes, manifest);
	const ageVerificationTime = checkAttr('ageVerificationTime', attributes, manifest);

	return (
		<>
			<Toggle
				label={label}
				checked={ageVerificationUse}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationUse', attributes, manifest)]: value })}
			>
			</Toggle>
			<TextControl
				label='Confirm Button Text'
				value={ageVerificationConfirmText}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationConfirmText', attributes, manifest)]: value })}
			/>
			<TextControl
				label='Decline Button Text'
				value={ageVerificationDeclineText}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationDeclineText', attributes, manifest)]: value })}
			/>
			<TextControl
				label='Decline Button Link'
				value={ageVerificationDeclineLink}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationDeclineLink', attributes, manifest)]: value })}
			/>
			<NumberPicker
				label='Cookie Expiration Duration'
				value={ageVerificationTime}
				onChange={(value) => setAttributes({ [getAttrKey('ageVerificationTime', attributes, manifest)]: value })}
				max={24}
			/>
		</>
	);
};
