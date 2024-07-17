import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, selector, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const AgeVerificationEditor = (attributes) => {

	const ageVerificationUse = checkAttr('ageVerificationUse', attributes, manifest);

	if (!ageVerificationUse) {
		return null;
	}

	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		additionalClass,
		blockClass,
		onClick,
	} = attributes;
	
	const ageVerificationContent = checkAttr('ageVerificationContent', attributes, manifest);
	const ageVerificationConfirmText = checkAttr('ageVerificationConfirmText', attributes, manifest);
	const ageVerificationDeclineText = checkAttr('ageVerificationDeclineText', attributes, manifest);

	const ageVerificationClass = classnames(
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	);

	const ageVerificationOverlayClass = selector(componentClass, componentClass, 'overlay');
	const ageVerificationDialogClass = selector(componentClass, componentClass, 'dialog');
	const ageVerificationContentClass = selector(componentClass, componentClass, 'content');
	const ageVerificationCloseClass = selector(componentClass, componentClass, 'close');
	const ageVerificationConfirmButtonClass = selector(componentClass, componentClass, 'confirm-button');
	const ageVerificationDeclineButtonClass = selector(componentClass, componentClass, 'decline-button');

	return (
		<div className={ageVerificationClass} aria-hidden="false">
			<div className={ageVerificationOverlayClass} tabIndex="-1">
				<div className={ageVerificationDialogClass} role="dialog" aria-age-verification="true">
					<div className={ageVerificationContentClass}>
						{ageVerificationContent}
					</div>
					<div clasName={ageVerificationCloseClass}>
						<button
							className={ageVerificationConfirmButtonClass}
							aria-label={__('Age Verification Confirm', 'delta9-digital-blocks-plugin')}
							onClick={onClick}
						>
							{ageVerificationConfirmText}
						</button>
						<button
							className={ageVerificationDeclineButtonClass}
							aria-label={__('Age Verification Decline', 'delta9-digital-blocks-plugin')}
							onClick={onClick}
						>
							{ageVerificationDeclineText}
						</button>
					</div>
				</div>
			</div>
		</div>
	);
};
