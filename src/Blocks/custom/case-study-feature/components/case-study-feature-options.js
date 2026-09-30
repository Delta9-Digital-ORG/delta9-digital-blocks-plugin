import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl, ToggleControl, Spinner } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { useWorkPostOptions } from './use-work-posts';

export const CaseStudyFeatureOptions = ({ attributes, setAttributes }) => {
	const options = useWorkPostOptions();
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Case study', 'delta9-digital-blocks-plugin')}>
			{options ? (
				<SelectControl
					label={__('Work post', 'delta9-digital-blocks-plugin')}
					help={__('Screenshots, logo, domain, stats and service tags come from this post.', 'delta9-digital-blocks-plugin')}
					value={checkAttr('caseStudyFeatureWorkId', attributes, manifest)}
					options={[{ label: __('Select a work post', 'delta9-digital-blocks-plugin'), value: 0 }, ...options]}
					onChange={(v) => setAttr('caseStudyFeatureWorkId', parseInt(v, 10) || 0)}
				/>
			) : <Spinner />}
			<TextControl
				label={__('Button label', 'delta9-digital-blocks-plugin')}
				value={checkAttr('caseStudyFeatureCtaLabel', attributes, manifest)}
				onChange={(v) => setAttr('caseStudyFeatureCtaLabel', v)}
			/>
			<TextControl
				label={__('Tags (comma separated)', 'delta9-digital-blocks-plugin')}
				help={__('Optional. Replaces the work post\'s services, e.g. per service page.', 'delta9-digital-blocks-plugin')}
				value={checkAttr('caseStudyFeatureTags', attributes, manifest)}
				onChange={(v) => setAttr('caseStudyFeatureTags', v)}
			/>
			<ToggleControl
				label={__('Show phone frame', 'delta9-digital-blocks-plugin')}
				checked={checkAttr('caseStudyFeatureShowPhone', attributes, manifest)}
				onChange={(v) => setAttr('caseStudyFeatureShowPhone', v)}
			/>
		</PanelBody>
	);
};
