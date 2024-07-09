import React, { useState, useEffect } from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { selector, props, checkAttr, getAttrKey} from '@eightshift/frontend-libs/scripts';
import { ModalEditor as ModalEditorComponent } from '../../../components/modal/components/modal-editor';
import { ModalButtonEditor } from '../../../components/modal-button/components/modal-button-editor';
import manifest from '../manifest.json';

export const ModalContentEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const modalContentStartOpen = checkAttr('modalContentStartOpen', attributes, manifest);
	const modalContentTriggerClass = selector(blockClass, blockClass, 'trigger');
	const modalContentContentClass = selector(blockClass, blockClass, 'content');

	const [open, setOpen] = useState(modalContentStartOpen);

	useEffect(() => {
		setOpen(modalContentStartOpen);
	}, [modalContentStartOpen]);


	return (
		<div className={blockClass} data-open={open}>
			<div className={modalContentTriggerClass}>
				<ModalButtonEditor
				{...props('modal-button', attributes, {
					additionalClass: modalContentTriggerClass,
					setAttributes,
				})}
			/>
		</div>
		<div className={modalContentContentClass}>
			<ModalEditorComponent
				{...props('modal', attributes, {
					additionalClass: open ? 'is-open' : '',
					modalConent: <InnerBlocks />
				})}
				onClick={() => setAttributes({ [getAttrKey('modalContentStartOpen', attributes, manifest)]: false })}
			/>
		</div>
	</div>
	);
};
