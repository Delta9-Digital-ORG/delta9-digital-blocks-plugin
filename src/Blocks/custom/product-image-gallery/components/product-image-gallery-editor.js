import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { Placeholder, Button, Spinner, TextControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { createBlock } from '@wordpress/blocks';

// Allowed inner blocks. Core image is the default insert; admins can use
// the WP "Transform to" menu on any individual image to switch it to a
// cover block (image → cover is a built-in core transform).
const ALLOWED_BLOCKS = ['core/image', 'core/cover', 'core/group', 'core/columns'];

export const ProductImageGalleryEditor = (props) => {
	const { attributes, setAttributes, clientId } = props;
	const { productImageGalleryLoaded, productImageGalleryProductId } = attributes;
	const blockProps = useBlockProps();
	const { insertBlocks } = useDispatch('core/block-editor');
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState('');

	// Resolve product context — explicit attribute override wins, otherwise
	// fall back to the current post if it's a product CPT.
	const { currentPostId, currentPostType } = useSelect(
		(select) => ({
			currentPostId: select('core/editor')?.getCurrentPostId() ?? 0,
			currentPostType: select('core/editor')?.getCurrentPostType() ?? '',
		}),
		[]
	);

	const productId = productImageGalleryProductId || (currentPostType === 'product' ? currentPostId : 0);

	const loadGallery = async () => {
		if (!productId) {
			setError(__('No product context — open a product to edit, or set a Product ID in the sidebar.', 'delta9-digital-blocks-plugin'));
			return;
		}
		setIsLoading(true);
		setError('');
		try {
			// WC Store API exposes products with their gallery as an `images` array.
			const data = await apiFetch({ path: `/wc/store/v1/products/${productId}` });
			const images = Array.isArray(data?.images) ? data.images : [];

			if (images.length === 0) {
				setError(__('This product has no gallery images.', 'delta9-digital-blocks-plugin'));
				setIsLoading(false);
				return;
			}

			// Spawn one core/image block per gallery image.
			const imageBlocks = images.map((img) =>
				createBlock('core/image', {
					id: img.id,
					url: img.src,
					alt: img.alt || '',
					caption: img.caption || '',
				})
			);

			await insertBlocks(imageBlocks, undefined, clientId);
			setAttributes({ productImageGalleryLoaded: true });
		} catch (err) {
			setError(err?.message || __('Failed to load product gallery.', 'delta9-digital-blocks-plugin'));
		}
		setIsLoading(false);
	};

	const resetBlock = () => {
		setAttributes({ productImageGalleryLoaded: false });
		setError('');
	};

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={__('Source', 'delta9-digital-blocks-plugin')} initialOpen={true}>
					<TextControl
						label={__('Product ID override', 'delta9-digital-blocks-plugin')}
						help={__('Leave blank to use the current product. Set a specific ID to load that product\'s gallery.', 'delta9-digital-blocks-plugin')}
						type="number"
						value={productImageGalleryProductId || ''}
						onChange={(value) => setAttributes({ productImageGalleryProductId: parseInt(value, 10) || 0 })}
					/>
					<Button variant="secondary" onClick={resetBlock} style={{ marginTop: '12px' }}>
						{__('Show "Load from gallery" placeholder again', 'delta9-digital-blocks-plugin')}
					</Button>
				</PanelBody>
			</InspectorControls>

			{!productImageGalleryLoaded && (
				<Placeholder
					label={__('Product Image Gallery', 'delta9-digital-blocks-plugin')}
					instructions={
						productId
							? __('Load images from this product\'s gallery as nested core blocks. After loading, you can reorder them by drag or change individual images into cover blocks via the "Transform to" menu.', 'delta9-digital-blocks-plugin')
							: __('This block needs a product context. Open a product to edit, or set a Product ID in the sidebar.', 'delta9-digital-blocks-plugin')
					}
				>
					{error && <p style={{ color: '#c00', width: '100%' }}>{error}</p>}
					<Button
						variant="primary"
						onClick={loadGallery}
						disabled={!productId || isLoading}
					>
						{isLoading ? <Spinner /> : __('Load from product gallery', 'delta9-digital-blocks-plugin')}
					</Button>
				</Placeholder>
			)}

			<InnerBlocks
				allowedBlocks={ALLOWED_BLOCKS}
				templateLock={false}
			/>
		</div>
	);
};
