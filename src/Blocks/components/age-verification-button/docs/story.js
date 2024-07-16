import React from 'react';
import { getExample, props, getOptions } from '@eightshift/frontend-libs/scripts';
import readme from './readme.mdx';
import manifest from './../manifest.json';
import { ModalButtonEditor } from '../components/load-more-editor';
import { ModalButtonOptions } from '../components/load-more-options';

export default {
	title: `Components/${manifest.title}`,
	parameters: {
		docs: { 
			page: readme
		}
	},
};

const attributes = getExample('modalButton', manifest);

export const editor = () => (
	<ModalButtonEditor {...props('modalButton', attributes)} />
);

export const options = () => (
	<ModalButtonOptions
		{...props('modalButton', attributes, {
			options: getOptions(attributes, manifest),
		})}
	/>
);
