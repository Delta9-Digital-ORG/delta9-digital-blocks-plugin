import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { fixtureFlavors } from './single-product-fixtures';

// Editor preview — a static JSX port of single-product.php using the same
// .yb-single-product BEM classes, so it picks up the frontend styles that
// applicationBlocks.css ships to the editor via enqueue_block_assets.
//
// Data source is context-aware: when the block is edited inside a product
// post (post.php on a `product`), the real flavor state is fetched from the
// SingleProductPreviewRoute REST endpoint — the same builder the frontend
// render uses — so the preview shows the actual product and its siblings.
// In template context (Site Editor edits one template for ALL products, so
// there is no current product ID) it falls back to bundled fixtures.
//
// Flavor cards, tabs, pack picker, and the qty stepper are wired to local
// React state so authors can preview the per-flavor color theming.

const TABS = [
	{ key: 'description', label: __('Description', 'delta9-digital-blocks-plugin') },
	{ key: 'cannafacts', label: __('Nutritional facts', 'delta9-digital-blocks-plugin') },
	{ key: 'benefits', label: __('Benefits', 'delta9-digital-blocks-plugin') },
];

export const SingleProductEditor = ({ attributes }) => {
	const {
		singleProductBadgeUrl,
		singleProductBadgeAlt,
		singleProductBadges,
	} = attributes;

	const { postId, postType } = useSelect((select) => {
		const editor = select('core/editor');
		return {
			postId: editor?.getCurrentPostId?.(),
			postType: editor?.getCurrentPostType?.(),
		};
	}, []);

	const [remote, setRemote] = useState(null);
	const [selectedId, setSelectedId] = useState(null);
	const [tab, setTab] = useState('description');
	const [packIndex, setPackIndex] = useState(0);
	const [qty, setQty] = useState(null);

	useEffect(() => {
		if (postType !== 'product' || !postId) {
			return;
		}
		apiFetch({ path: `/delta9-digital-blocks-plugin/v1/single-product/preview?productId=${postId}` })
			.then((response) => {
				if (response?.success && response?.flavors?.length) {
					setRemote(response);
				}
			})
			.catch(() => {});
	}, [postId, postType]);

	const flavors = remote?.flavors ?? fixtureFlavors;
	const activeId = selectedId ?? remote?.activeId ?? flavors[0].id;
	const flavor = flavors.find((f) => f.id === activeId) ?? flavors[0];

	const packs = flavor.packOptions ?? [];
	const pack = packs[packIndex] ?? packs[0];
	const displayQty = qty ?? pack?.quantity ?? 1;
	const displayPrice = pack?.priceHtml ?? flavor.priceHtml ?? '';

	// Same badge resolution as the PHP render: array attribute → legacy
	// single URL/alt pair → nothing. No default badge, because most products
	// have not won an award; unfilled repeater rows are dropped as well, so
	// the preview shows what the page will actually print.
	let badges = [];
	if (singleProductBadges?.length) {
		badges = singleProductBadges
			.filter((b) => b?.url)
			.map((b) => ({ url: b.url, alt: b.alt ?? '' }));
	} else if (singleProductBadgeUrl) {
		badges = [{ url: singleProductBadgeUrl, alt: singleProductBadgeAlt }];
	}

	const description = flavor.description || '';

	// Mirrors the front-end getter: no heading in the panel, the description
	// prints whole, and the nutrition tab shows only its table — the
	// serving-size copy that used to sit above it was dropped.
	const panelBody = tab === 'description'
		? description
		: (tab === 'cannafacts' ? '' : (flavor[tab] || ''));

	const selectFlavor = (id) => {
		setSelectedId(id);
		setPackIndex(0);
		setQty(null);
	};

	return (
		<section
			className='yb-single-product'
			style={{
				'--page-bg': flavor.pageBg || '#fff',
				'--page-contrast': flavor.pageContrast || '#117571',
				'--name-color': flavor.nameColor || '#117571',
			}}
		>
			<div className='yb-single-product__inner'>

				<div className='yb-single-product__hero'>

					<div className='yb-single-product__flavors'>
						{flavors.map((f) => (
							<button
								type='button'
								key={f.id}
								className={`yb-single-product__flavorCard ${f.id === activeId ? 'is-active' : ''}`}
								style={{ '--flavor-bg': f.cardBg || '#ccc', '--flavor-name': f.nameColor || '#117571' }}
								onClick={() => selectFlavor(f.id)}
							>
								<div className='yb-single-product__flavorCard__image'>
									<img src={f.cardImage || f.image} alt='' />
								</div>
								<span className='yb-single-product__flavorCard__label'>{f.cardLabel ?? f.name}</span>
							</button>
						))}
					</div>

					<div className='yb-single-product__heroImage'>
						<img src={flavor.image} alt={flavor.name} />
					</div>

					<div className='yb-single-product__panel'>
						<div className='yb-single-product__tabs'>
							{TABS.map((t) => (
								<button
									type='button'
									key={t.key}
									className={t.key === tab ? 'is-active' : ''}
									onClick={() => setTab(t.key)}
								>
									{t.label}
								</button>
							))}
						</div>

						<div className='yb-single-product__panelCard'>
							<div className='yb-single-product__panelCard__body'>{panelBody}</div>
						</div>
					</div>

				</div>

				<div className='yb-single-product__purchase'>
					<div className='yb-single-product__purchaseTop'>
						<div className='yb-single-product__awards'>
							{badges.map((badge, index) => (
								<img
									key={index}
									className='yb-single-product__award'
									src={badge.url}
									alt={badge.alt}
								/>
							))}
						</div>

						<div className='yb-single-product__buyCard'>
							<div className='yb-single-product__buyTop'>
								<div>
									<div className='yb-single-product__stars'>
										<span>
											{`(${flavor.starsAvg} stars) • ${flavor.reviewCount} reviews`}
										</span>
									</div>
									<h2>{flavor.name}</h2>
								</div>
								<div className='yb-single-product__price'>
									<strong>{displayPrice}</strong>
									{flavor.mood &&
										<span className='yb-single-product__mood'>{flavor.mood}</span>
									}
								</div>
							</div>

							{packs.length > 0 &&
								<div className='yb-single-product__sizePickerWrap'>
									<select
										className='yb-single-product__sizePicker'
										value={packIndex}
										onChange={(event) => {
											const index = parseInt(event.target.value, 10) || 0;
											setPackIndex(index);
											setQty(packs[index]?.quantity ?? 1);
										}}
										aria-label={__('Pack size', 'delta9-digital-blocks-plugin')}
									>
										{packs.map((opt, index) => (
											<option key={index} value={index}>{`${opt.label} — ${opt.priceHtml}`}</option>
										))}
									</select>
								</div>
							}

							<div className='yb-single-product__buyBottom'>
								<div className='yb-single-product__qty'>
									<button type='button' onClick={() => setQty(Math.max(1, displayQty - 1))} aria-label={__('Decrease quantity', 'delta9-digital-blocks-plugin')}>−</button>
									<span className='yb-single-product__qtyInput'>{displayQty}</span>
									<button type='button' onClick={() => setQty(displayQty + 1)} aria-label={__('Increase quantity', 'delta9-digital-blocks-plugin')}>+</button>
								</div>
								<button type='button' className='yb-single-product__addToCart'>
									{__('Add To Cart', 'delta9-digital-blocks-plugin')}
								</button>
							</div>
						</div>
					</div>

					<div className='yb-single-product__wordmark'>{flavor.name}</div>
				</div>

			</div>
		</section>
	);
};
