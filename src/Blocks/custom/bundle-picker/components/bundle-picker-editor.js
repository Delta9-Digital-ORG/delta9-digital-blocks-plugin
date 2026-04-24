import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const BundlePickerEditor = ({ attributes }) => {
	const {
		blockFullName,
	} = attributes;

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={{
				...attributes,
				bundlePickerServerSideRender: true,
				wrapperUse: false,
			}}
		/>
	);
};
