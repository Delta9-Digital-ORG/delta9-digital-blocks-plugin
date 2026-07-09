import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, BlockControls } from '@wordpress/block-editor';
import {
	Placeholder,
	Spinner,
	PanelBody,
	TextControl,
	RadioControl,
	RangeControl,
	SelectControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// Cache resolved product responses across all slot instances on the page
// so opening a template with N slots only triggers one Store API request
// per unique product ID, not N.
const productCache = new Map();

async function fetchProduct(productId) {
	if (!productId) return null;
	const key = String(productId);
	if (productCache.has(key)) return productCache.get(key);
	const promise = apiFetch({ path: `/wc/store/v1/products/${productId}` })
		.catch(() => null);
	productCache.set(key, promise);
	return promise;
}

export const ProductImageGallerySlotEditor = (props) => {
	const { attributes, setAttributes, context } = props;
	const {
		productImageGallerySlotIndex,
		productImageGallerySlotType,
		productImageGallerySlotProductId,
		productImageGallerySlotPreviewProductId,
		productImageGallerySlotCoverMinHeight,
		productImageGallerySlotCoverDimRatio,
		productImageGallerySlotCoverOverlayColor,
		productImageGallerySlotMinHeight,
		productImageGallerySlotObjectFit,
	} = attributes;

	const blockProps = useBlockProps();

	// Editor preview resolution order:
	//   1. Explicit "preview product" override on the block (Inspector field).
	//   2. The block's runtime productId override (also used at render).
	//   3. The query-loop / template `postId` context if the post type is product.
	//   4. The current edited post if it's a product CPT.
	//   5. Fallback: first published product (fetched on mount once).
	const { currentPostId, currentPostType } = useSelect(
		(select) => ({
			currentPostId: select('core/editor')?.getCurrentPostId() ?? 0,
			currentPostType: select('core/editor')?.getCurrentPostType() ?? '',
		}),
		[]
	);

	const contextPostId = context?.postId ?? 0;
	const contextPostType = context?.postType ?? '';

	const resolvedPreviewId =
		productImageGallerySlotPreviewProductId ||
		productImageGallerySlotProductId ||
		(contextPostType === 'product' ? contextPostId : 0) ||
		(currentPostType === 'product' ? currentPostId : 0) ||
		0;

	const [previewProduct, setPreviewProduct] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [fallbackProductId, setFallbackProductId] = useState(0);

	// On mount, if we have no preview source, fetch the first published
	// product so the editor can still show *something* in template / Site
	// Editor contexts.
	useEffect(() => {
		if (resolvedPreviewId) return;
		if (fallbackProductId) return;
		apiFetch({ path: '/wc/store/v1/products?per_page=1' })
			.then((list) => {
				if (Array.isArray(list) && list[0]?.id) {
					setFallbackProductId(list[0].id);
				}
			})
			.catch(() => {});
	}, [resolvedPreviewId, fallbackProductId]);

	const effectivePreviewId = resolvedPreviewId || fallbackProductId;

	useEffect(() => {
		let cancelled = false;
		if (!effectivePreviewId) {
			setPreviewProduct(null);
			return () => {};
		}
		setIsLoading(true);
		fetchProduct(effectivePreviewId).then((data) => {
			if (cancelled) return;
			setPreviewProduct(data);
			setIsLoading(false);
		});
		return () => {
			cancelled = true;
		};
	}, [effectivePreviewId]);

	const images = Array.isArray(previewProduct?.images) ? previewProduct.images : [];
	const slotImage = images[productImageGallerySlotIndex] || null;
	const isCover = productImageGallerySlotType === 'cover';

	const toggleType = () => {
		setAttributes({
			productImageGallerySlotType: isCover ? 'image' : 'cover',
		});
	};

	const renderPreview = () => {
		if (isLoading) return <Spinner />;

		if (!effectivePreviewId) {
			return (
				<Placeholder
					label={__('Product Gallery Slot', 'delta9-digital-blocks-plugin')}
					instructions={__('No product to preview. Set a preview product ID in the sidebar, or place this block on a product page / inside a product query loop.', 'delta9-digital-blocks-plugin')}
				/>
			);
		}

		if (!slotImage) {
			return (
				<Placeholder
					label={__('Product Gallery Slot', 'delta9-digital-blocks-plugin')}
					instructions={__('No image at position %d for the preview product. Lower the index or remove this slot.', 'delta9-digital-blocks-plugin').replace('%d', productImageGallerySlotIndex)}
				/>
			);
		}

		const badge = (
			<span
				style={{
					position: 'absolute',
					top: 8,
					left: 8,
					padding: '4px 8px',
					background: 'rgba(0,0,0,0.7)',
					color: '#fff',
					fontSize: 11,
					fontWeight: 600,
					borderRadius: 4,
					textTransform: 'uppercase',
					letterSpacing: '0.04em',
					zIndex: 2,
				}}
			>
				{`${__('Slot', 'delta9-digital-blocks-plugin')} ${productImageGallerySlotIndex} · ${isCover ? __('Cover', 'delta9-digital-blocks-plugin') : __('Image', 'delta9-digital-blocks-plugin')}`}
			</span>
		);

		const effectiveCoverHeight = productImageGallerySlotMinHeight || productImageGallerySlotCoverMinHeight || '400px';

		if (isCover) {
			return (
				<div
					style={{
						position: 'relative',
						minHeight: effectiveCoverHeight,
						overflow: 'hidden',
					}}
				>
					{badge}
					<img
						src={slotImage.src}
						alt={slotImage.alt || ''}
						style={{
							position: 'absolute',
							inset: 0,
							width: '100%',
							height: '100%',
							objectFit: 'cover',
						}}
					/>
					<span
						aria-hidden="true"
						style={{
							position: 'absolute',
							inset: 0,
							background: productImageGallerySlotCoverOverlayColor || '#000',
							opacity: (productImageGallerySlotCoverDimRatio ?? 50) / 100,
						}}
					/>
				</div>
			);
		}

		// Image variant. When a minHeight is set, treat the figure as a
		// fixed-frame crop so it matches the frontend render.
		const hasHeight = !!productImageGallerySlotMinHeight;
		return (
			<figure
				style={{
					position: 'relative',
					margin: 0,
					...(hasHeight ? { minHeight: productImageGallerySlotMinHeight, overflow: 'hidden' } : {}),
				}}
			>
				{badge}
				<img
					src={slotImage.src}
					alt={slotImage.alt || ''}
					style={
						hasHeight
							? { width: '100%', height: '100%', objectFit: productImageGallerySlotObjectFit || 'cover', display: 'block' }
							: { width: '100%', height: 'auto', display: 'block' }
					}
				/>
			</figure>
		);
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={isCover ? 'format-image' : 'cover-image'}
						label={isCover ? __('Convert to Image', 'delta9-digital-blocks-plugin') : __('Convert to Cover', 'delta9-digital-blocks-plugin')}
						onClick={toggleType}
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={__('Slot', 'delta9-digital-blocks-plugin')} initialOpen={true}>
					<TextControl
						label={__('Gallery position (0-indexed)', 'delta9-digital-blocks-plugin')}
						type="number"
						min={0}
						value={productImageGallerySlotIndex}
						onChange={(v) => setAttributes({ productImageGallerySlotIndex: parseInt(v, 10) || 0 })}
					/>
					<RadioControl
						label={__('Slot type', 'delta9-digital-blocks-plugin')}
						selected={productImageGallerySlotType}
						options={[
							{ label: __('Image', 'delta9-digital-blocks-plugin'), value: 'image' },
							{ label: __('Cover', 'delta9-digital-blocks-plugin'), value: 'cover' },
						]}
						onChange={(v) => setAttributes({ productImageGallerySlotType: v })}
					/>
					<TextControl
						label={__('Min height (CSS, e.g. 393px)', 'delta9-digital-blocks-plugin')}
						help={__('Optional. When set, the image is cropped to this height using object-fit.', 'delta9-digital-blocks-plugin')}
						value={productImageGallerySlotMinHeight || ''}
						onChange={(v) => setAttributes({ productImageGallerySlotMinHeight: v })}
					/>
					<SelectControl
						label={__('Object fit', 'delta9-digital-blocks-plugin')}
						value={productImageGallerySlotObjectFit || 'cover'}
						options={[
							{ label: 'cover', value: 'cover' },
							{ label: 'contain', value: 'contain' },
							{ label: 'fill', value: 'fill' },
							{ label: 'none', value: 'none' },
							{ label: 'scale-down', value: 'scale-down' },
						]}
						onChange={(v) => setAttributes({ productImageGallerySlotObjectFit: v })}
					/>
				</PanelBody>

				{isCover && (
					<PanelBody title={__('Cover options', 'delta9-digital-blocks-plugin')} initialOpen={true}>
						<TextControl
							label={__('Min height (CSS)', 'delta9-digital-blocks-plugin')}
							value={productImageGallerySlotCoverMinHeight}
							onChange={(v) => setAttributes({ productImageGallerySlotCoverMinHeight: v })}
						/>
						<RangeControl
							label={__('Overlay dim %', 'delta9-digital-blocks-plugin')}
							min={0}
							max={100}
							step={5}
							value={productImageGallerySlotCoverDimRatio}
							onChange={(v) => setAttributes({ productImageGallerySlotCoverDimRatio: v })}
						/>
						<TextControl
							label={__('Overlay color (hex)', 'delta9-digital-blocks-plugin')}
							value={productImageGallerySlotCoverOverlayColor}
							onChange={(v) => setAttributes({ productImageGallerySlotCoverOverlayColor: v })}
						/>
					</PanelBody>
				)}

				<PanelBody title={__('Source', 'delta9-digital-blocks-plugin')} initialOpen={false}>
					<TextControl
						label={__('Runtime product ID override', 'delta9-digital-blocks-plugin')}
						help={__('Leave 0 to resolve from the current page or loop context. Set a specific ID to always read that product\'s gallery.', 'delta9-digital-blocks-plugin')}
						type="number"
						min={0}
						value={productImageGallerySlotProductId || ''}
						onChange={(v) => setAttributes({ productImageGallerySlotProductId: parseInt(v, 10) || 0 })}
					/>
					<TextControl
						label={__('Preview-only product ID', 'delta9-digital-blocks-plugin')}
						help={__('Used in the editor only to choose which product\'s gallery to display in the preview. Has no effect on the frontend.', 'delta9-digital-blocks-plugin')}
						type="number"
						min={0}
						value={productImageGallerySlotPreviewProductId || ''}
						onChange={(v) => setAttributes({ productImageGallerySlotPreviewProductId: parseInt(v, 10) || 0 })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>{renderPreview()}</div>
		</>
	);
};
