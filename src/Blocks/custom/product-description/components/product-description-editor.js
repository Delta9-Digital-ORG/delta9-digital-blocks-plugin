import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Spinner, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// Shared cache so repeated slot blocks on the same page only fetch each
// product once.
const productCache = new Map();

async function fetchProduct(productId) {
	if (!productId) return null;
	const key = String(productId);
	if (productCache.has(key)) return productCache.get(key);
	const promise = apiFetch({ path: `/wp/v2/product/${productId}` }).catch(() => null);
	productCache.set(key, promise);
	return promise;
}

function splitParagraphs(value) {
	if (!value) return [];
	// Existing product meta uses ' | ' (with spaces) as a paragraph break;
	// also support newline-separated content for free-form admin entries.
	return String(value)
		.split(/\n|\s\|\s/g)
		.map((s) => s.trim())
		.filter(Boolean);
}

export const ProductDescriptionEditor = (props) => {
	const { attributes, setAttributes } = props;
	const { productDescriptionHeading, productDescriptionProductId } = attributes;
	const blockProps = useBlockProps();

	const { currentPostId, currentPostType } = useSelect(
		(select) => ({
			currentPostId: select('core/editor')?.getCurrentPostId() ?? 0,
			currentPostType: select('core/editor')?.getCurrentPostType() ?? '',
		}),
		[]
	);

	const previewId =
		productDescriptionProductId ||
		(currentPostType === 'product' ? currentPostId : 0);

	const [preview, setPreview] = useState(null);
	const [loading, setLoading] = useState(false);

	useEffect(() => {
		let cancelled = false;
		if (!previewId) {
			setPreview(null);
			return () => {};
		}
		setLoading(true);
		fetchProduct(previewId).then((data) => {
			if (cancelled) return;
			setPreview(data);
			setLoading(false);
		});
		return () => {
			cancelled = true;
		};
	}, [previewId]);

	const meta = preview?.meta ?? {};
	const description = meta._custom_product_description_text_field || '';
	const ingredients = meta._custom_product_ingredients_text_field || '';
	const otherIngredients = meta._custom_product_other_ingredients_text_field || '';
	const ingredientParas = splitParagraphs(ingredients);

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Product Description', 'delta9-digital-blocks-plugin')} initialOpen={true}>
					<TextControl
						label={__('Heading', 'delta9-digital-blocks-plugin')}
						value={productDescriptionHeading}
						onChange={(v) => setAttributes({ productDescriptionHeading: v })}
					/>
					<TextControl
						label={__('Product ID override', 'delta9-digital-blocks-plugin')}
						help={__('Leave 0 to use the current product / loop context.', 'delta9-digital-blocks-plugin')}
						type="number"
						min={0}
						value={productDescriptionProductId || ''}
						onChange={(v) => setAttributes({ productDescriptionProductId: parseInt(v, 10) || 0 })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="product-description product-description--editor-preview">
					{loading && <Spinner />}
					{!loading && !previewId && (
						<Placeholder
							label={__('Product Description', 'delta9-digital-blocks-plugin')}
							instructions={__('No product context — set a product ID or place on a product page.', 'delta9-digital-blocks-plugin')}
						/>
					)}
					{!loading && previewId && (
						<>
							<h3 className="product-description__heading">{productDescriptionHeading}</h3>
							{description && <p className="product-description__intro">{description}</p>}
							{ingredientParas.length > 0 && (
								<div className="product-description__body">
									{ingredientParas.map((p, i) => (
										<p key={i}>{p}</p>
									))}
								</div>
							)}
							{otherIngredients && (
								<p className="product-description__other">
									<strong>{__('Other Ingredients:', 'delta9-digital-blocks-plugin')}</strong>{' '}
									{otherIngredients.replace(/^[^:]*:\s*/, '')}
								</p>
							)}
						</>
					)}
				</div>
			</div>
		</>
	);
};
