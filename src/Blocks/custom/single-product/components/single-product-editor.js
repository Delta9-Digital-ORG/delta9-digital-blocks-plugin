import React from 'react';

export const SingleProductEditor = () => {
	return (
		<div
			className="single-product-editor-preview"
			style={{
				border: '1px dashed #999',
				padding: 24,
				textAlign: 'center',
				fontFamily: 'system-ui, sans-serif',
				color: '#444',
			}}
		>
			<strong>Single Product</strong>
			<br />
			<small>Editor preview lands in Phase 2 — port from <code>_mockups/single-product/</code>.</small>
		</div>
	);
};
