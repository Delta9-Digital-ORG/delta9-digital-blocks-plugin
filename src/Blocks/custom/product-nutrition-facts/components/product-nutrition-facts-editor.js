import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Spinner, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const productCache = new Map();

async function fetchProduct(productId) {
	if (!productId) return null;
	const key = String(productId);
	if (productCache.has(key)) return productCache.get(key);
	const promise = apiFetch({ path: `/wp/v2/product/${productId}` }).catch(() => null);
	productCache.set(key, promise);
	return promise;
}

// Each row is "Label|Value" on its own line. Empty / malformed lines
// are skipped. Returns [{label, value}, ...].
function parseRows(value) {
	if (!value) return [];
	return String(value)
		.split(/\r\n|\r|\n/)
		.map((line) => {
			const [label, ...rest] = line.split('|');
			if (!label || rest.length === 0) return null;
			return { label: label.trim(), value: rest.join('|').trim() };
		})
		.filter(Boolean);
}

export const ProductNutritionFactsEditor = (props) => {
	const { attributes, setAttributes } = props;
	const {
		productNutritionFactsHeading,
		productNutritionFactsCanaHeading,
		productNutritionFactsProductId,
	} = attributes;
	const blockProps = useBlockProps();

	const { currentPostId, currentPostType } = useSelect(
		(select) => ({
			currentPostId: select('core/editor')?.getCurrentPostId() ?? 0,
			currentPostType: select('core/editor')?.getCurrentPostType() ?? '',
		}),
		[]
	);

	const previewId =
		productNutritionFactsProductId ||
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
	const nutritionRows = parseRows(meta._yb_nutrition_rows);
	const canaRows = parseRows(meta._yb_cana_rows);
	const servingSize = meta._custom_product_serving_size_text_field || '';
	const servingsPerContainer = meta._custom_product_servings_per_container_text_field || '';
	const suggestedUse = meta._custom_product_suggested_use_text_field || '';

	const renderTable = (rows) =>
		rows.map((r, i) => (
			<div key={i} className="product-nutrition-facts__row">
				<span className="product-nutrition-facts__rowLabel">{r.label}</span>
				<span className="product-nutrition-facts__rowValue">{r.value}</span>
			</div>
		));

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Headings', 'delta9-digital-blocks-plugin')} initialOpen={true}>
					<TextControl
						label={__('Nutrition heading', 'delta9-digital-blocks-plugin')}
						value={productNutritionFactsHeading}
						onChange={(v) => setAttributes({ productNutritionFactsHeading: v })}
					/>
					<TextControl
						label={__('Cana Facts heading', 'delta9-digital-blocks-plugin')}
						value={productNutritionFactsCanaHeading}
						onChange={(v) => setAttributes({ productNutritionFactsCanaHeading: v })}
					/>
				</PanelBody>
				<PanelBody title={__('Source', 'delta9-digital-blocks-plugin')} initialOpen={false}>
					<TextControl
						label={__('Product ID override', 'delta9-digital-blocks-plugin')}
						help={__('Leave 0 to use the current product / loop context.', 'delta9-digital-blocks-plugin')}
						type="number"
						min={0}
						value={productNutritionFactsProductId || ''}
						onChange={(v) => setAttributes({ productNutritionFactsProductId: parseInt(v, 10) || 0 })}
					/>
					<p style={{ fontSize: 12, color: '#666' }}>
						{__('Tables come from product meta `_yb_nutrition_rows` and `_yb_cana_rows` (one "Label|Value" per line).', 'delta9-digital-blocks-plugin')}
					</p>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="product-nutrition-facts product-nutrition-facts--editor-preview">
					{loading && <Spinner />}
					{!loading && !previewId && (
						<Placeholder
							label={__('Product Nutrition Facts', 'delta9-digital-blocks-plugin')}
							instructions={__('No product context — set a product ID or place on a product page.', 'delta9-digital-blocks-plugin')}
						/>
					)}
					{!loading && previewId && (
						<>
							{nutritionRows.length > 0 && (
								<>
									<h3 className="product-nutrition-facts__heading">{productNutritionFactsHeading}</h3>
									<div className="product-nutrition-facts__table">{renderTable(nutritionRows)}</div>
								</>
							)}
							{canaRows.length > 0 && (
								<>
									<h3 className="product-nutrition-facts__heading">{productNutritionFactsCanaHeading}</h3>
									<div className="product-nutrition-facts__table">{renderTable(canaRows)}</div>
								</>
							)}
							{(servingSize || servingsPerContainer || suggestedUse) && (
								<div className="product-nutrition-facts__notes">
									{servingSize && (
										<p>
											<strong>{__('Serving Size:', 'delta9-digital-blocks-plugin')}</strong> {servingSize}
										</p>
									)}
									{servingsPerContainer && (
										<p>
											<strong>{__('Servings Per Container:', 'delta9-digital-blocks-plugin')}</strong> {servingsPerContainer}
										</p>
									)}
									{suggestedUse && (
										<p>
											<strong>{__('Suggested Use:', 'delta9-digital-blocks-plugin')}</strong> {suggestedUse}
										</p>
									)}
								</div>
							)}
							{nutritionRows.length === 0 && canaRows.length === 0 && !servingSize && !servingsPerContainer && !suggestedUse && (
								<Placeholder
									label={__('Product Nutrition Facts', 'delta9-digital-blocks-plugin')}
									instructions={__('No nutrition data on this product yet. Add "Label|Value" rows to _yb_nutrition_rows / _yb_cana_rows in the product\'s sidebar (under Product Data).', 'delta9-digital-blocks-plugin')}
								/>
							)}
						</>
					)}
				</div>
			</div>
		</>
	);
};
