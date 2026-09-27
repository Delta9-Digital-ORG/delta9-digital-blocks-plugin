import React from 'react';
import { __ } from '@wordpress/i18n';
import { Placeholder, SelectControl, Spinner } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { useWorkPostOptions } from './use-work-posts';

export const CaseStudyFeatureEditor = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const workId = checkAttr('caseStudyFeatureWorkId', attributes, manifest);
	const options = useWorkPostOptions();

	if (!workId) {
		return (
			<Placeholder
				label={__('Case Study Feature', 'delta9-digital-blocks-plugin')}
				instructions={__('Pick the Work post to feature.', 'delta9-digital-blocks-plugin')}
			>
				{options ? (
					<SelectControl
						value={0}
						options={[{ label: __('Select a work post', 'delta9-digital-blocks-plugin'), value: 0 }, ...options]}
						onChange={(v) => setAttributes({ [getAttrKey('caseStudyFeatureWorkId', attributes, manifest)]: parseInt(v, 10) || 0 })}
					/>
				) : <Spinner />}
			</Placeholder>
		);
	}

	return (
		<ServerSideRender
			block={blockFullName}
			attributes={{
				...attributes,
				caseStudyFeatureServerSideRender: true,
			}}
		/>
	);
};
