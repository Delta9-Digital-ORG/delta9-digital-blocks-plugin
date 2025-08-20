import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';
import globalManifest from './../../../manifest.json';

export const ProductCategoryListEditor = ({ attributes, setAttributes }) => {
	const {
		blockFullName
	} = attributes;
	
	return (
		<ServerSideRender
			block={blockFullName}
			attributes={
				{
					...attributes,
					wrapperUse: false,
					productCategoryListServerSideRender: true,
				}
			}
		/>
	);
};
