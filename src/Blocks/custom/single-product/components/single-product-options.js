import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl, Button } from '@wordpress/components';
import { MediaPlaceholder } from '@wordpress/block-editor';
import {
	Repeater,
	RepeaterItem,
	icons,
	truncateMiddle,
} from '@eightshift/frontend-libs/scripts';

export const SingleProductOptions = ({ attributes, setAttributes }) => {
	const {
		singleProductBadgeUrl,
		singleProductBadgeAlt,
		singleProductBadges,
		singleProductServerSideRender,
	} = attributes;

	// Legacy migration — old content saved a single badge as a URL/alt pair.
	// Seed the array from it once so the repeater shows the existing badge;
	// the PHP render keeps its own legacy fallback for unmigrated content.
	const badges = (singleProductBadges?.length < 1 && singleProductBadgeUrl)
		? [{ id: 1, url: singleProductBadgeUrl, alt: singleProductBadgeAlt }]
		: (singleProductBadges ?? []);

	const updateBadge = (index, patch) => {
		const modified = [...badges];
		modified[index] = { ...modified[index], ...patch };
		setAttributes({ singleProductBadges: modified });
	};

	return (
		<PanelBody title={__('Single Product', 'delta9-digital-blocks-plugin')}>
			<Repeater
				icon={icons.image}
				label={__('Award badges', 'delta9-digital-blocks-plugin')}
				items={badges}
				attributeName='singleProductBadges'
				setAttributes={setAttributes}
			>
				{badges.map((badge, index) => (
					<RepeaterItem
						key={badge.id ?? index}
						icon={icons.image}
						title={badge.alt || __('Badge', 'delta9-digital-blocks-plugin')}
						subtitle={truncateMiddle(badge.url?.slice(badge.url?.lastIndexOf('/') + 1) ?? '', 20)}
					>
						{!badge.url &&
							<MediaPlaceholder
								icon={icons.image}
								accept={['image/*']}
								allowedTypes={['image']}
								labels={{ title: __('Badge image', 'delta9-digital-blocks-plugin') }}
								onSelect={(media) => updateBadge(index, { url: media.url, alt: badge.alt || media.alt || '' })}
							/>
						}

						{badge.url &&
							<>
								<img
									src={badge.url}
									alt={badge.alt ?? ''}
									style={{ maxWidth: '96px', maxHeight: '96px', display: 'block', margin: '0 0 8px' }}
								/>
								<Button
									icon={icons.trash}
									variant='secondary'
									isSmall
									onClick={() => updateBadge(index, { url: '' })}
								>
									{__('Replace image', 'delta9-digital-blocks-plugin')}
								</Button>
							</>
						}

						<TextControl
							label={__('Alt text', 'delta9-digital-blocks-plugin')}
							value={badge.alt ?? ''}
							onChange={(value) => updateBadge(index, { alt: value })}
						/>
					</RepeaterItem>
				))}
			</Repeater>

			<ToggleControl
				label={__('Server-side render', 'delta9-digital-blocks-plugin')}
				checked={singleProductServerSideRender}
				onChange={(value) => setAttributes({ singleProductServerSideRender: value })}
				help={__('Uses PHP render and seeds the Interactivity API state on the server.', 'delta9-digital-blocks-plugin')}
			/>
		</PanelBody>
	);
};
