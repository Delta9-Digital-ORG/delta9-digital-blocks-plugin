import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const MegaMenuEditor = ({ attributes }) => {
	const { blockFullName } = attributes;

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={{
				...attributes,
				megaMenuServerSideRender: true,
			}}
		/>
	);
};
