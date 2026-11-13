import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const TabOptions = ({ attributes, setAttributes }) => {
	const tabIconUrl = checkAttr('tabIconUrl', attributes, manifest);
	const tabIconId = checkAttr('tabIconId', attributes, manifest);

	const setIcon = (url, id) => setAttributes({
		[getAttrKey('tabIconUrl', attributes, manifest)]: url,
		[getAttrKey('tabIconId', attributes, manifest)]: id,
	});

	return (
		<PanelBody title={__('Tab', 'delta9-digital-blocks-plugin')}>
			<p>{__('Optional icon shown above the label in the tab list.', 'delta9-digital-blocks-plugin')}</p>
			{tabIconUrl && <img src={tabIconUrl} alt='' width={40} height={40} style={{ display: 'block', marginBottom: 8 }} />}
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={['image']}
					value={tabIconId}
					onSelect={(media) => setIcon(media.url, media.id)}
					render={({ open }) => (
						<Button variant='secondary' onClick={open}>
							{tabIconUrl ? __('Replace icon', 'delta9-digital-blocks-plugin') : __('Choose icon', 'delta9-digital-blocks-plugin')}
						</Button>
					)}
				/>
			</MediaUploadCheck>
			{tabIconUrl && (
				<Button variant='link' isDestructive onClick={() => setIcon('', 0)} style={{ marginLeft: 8 }}>
					{__('Remove', 'delta9-digital-blocks-plugin')}
				</Button>
			)}
		</PanelBody>
	);
};
