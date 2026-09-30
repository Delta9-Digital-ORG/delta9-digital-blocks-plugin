import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const InsightCardsEditor = ({ attributes }) => {
	const { blockFullName } = attributes;

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={{
				...attributes,
				insightCardsServerSideRender: true,
			}}
		/>
	);
};
