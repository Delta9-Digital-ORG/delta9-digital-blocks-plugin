import React, { useState, useEffect } from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { selector, props, checkAttr, getAttrKey} from '@eightshift/frontend-libs/scripts';
import { AgeVerificationEditor as AgeVerificationEditorComponent } from '../../../components/age-verification/components/age-verification-editor';
import manifest from '../manifest.json';

export const AgeVerificationContentEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const ageVerificationContentStartOpen = checkAttr('ageVerificationContentStartOpen', attributes, manifest);
	const ageVerificationContentTriggerClass = selector(blockClass, blockClass, 'trigger');
	const ageVerificationContentContentClass = selector(blockClass, blockClass, 'content');

	const [open, setOpen] = useState(ageVerificationContentStartOpen);

	useEffect(() => {
		setOpen(ageVerificationContentStartOpen);
	}, [ageVerificationContentStartOpen]);


	return (
		<div className={blockClass} data-open={open}>
			<div className={ageVerificationContentTriggerClass}>
				Age Verification Block
			</div>
			<div className={ageVerificationContentContentClass}>
				<AgeVerificationEditorComponent
					{...props('age-verification', attributes, {
						additionalClass: open ? 'is-open' : '',
						ageVerificationContent: <InnerBlocks />
					})}
					onClick={() => setAttributes({ [getAttrKey('ageVerificationContentStartOpen', attributes, manifest)]: false })}
				/>
			</div>
		</div>
	);
};
