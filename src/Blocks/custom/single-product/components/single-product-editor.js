import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import defaultBadge from '../assets/awards-badge.svg';
import { fixtureFlavors } from './single-product-fixtures';

// Editor preview — a static JSX port of single-product.php using the same
// .yb-single-product BEM classes, so it picks up the frontend styles that
// applicationBlocks.css ships to the editor via enqueue_block_assets.
// Product data is fixture-based (the block resolves real WC siblings only
// on the PHP side; the Site Editor template context has no product ID).
// Flavor cards, tabs, pack picker, and the qty stepper are wired to local
// React state so authors can preview the per-flavor color theming.

const TABS = [
	{ key: 'description', label: __('Description', 'delta9-digital-blocks-plugin') },
	{ key: 'cannafacts', label: __('Nutritional facts', 'delta9-digital-blocks-plugin') },
	{ key: 'ingredients', label: __('Ingredients', 'delta9-digital-blocks-plugin') },
];

export const SingleProductEditor = ({ attributes }) => {
	const {
		singleProductBadgeUrl,
		singleProductBadgeAlt,
		singleProductBadges,
	} = attributes;

	const [activeId, setActiveId] = useState(fixtureFlavors[0].id);
	const [tab, setTab] = useState('description');
	const [packIndex, setPackIndex] = useState(1);
	const [qty, setQty] = useState(4);

	const flavor = fixtureFlavors.find((f) => f.id === activeId) ?? fixtureFlavors[0];
	const pack = flavor.packOptions[packIndex] ?? flavor.packOptions[0];

	// Same badge resolution as the PHP render: array attribute → legacy
	// single URL/alt pair → bundled default SVG.
	let badges = [];
	if (singleProductBadges?.length) {
		badges = singleProductBadges.map((b) => ({ url: b.url || defaultBadge, alt: b.alt ?? '' }));
	} else if (singleProductBadgeUrl) {
		badges = [{ url: singleProductBadgeUrl, alt: singleProductBadgeAlt }];
	} else {
		badges = [{ url: defaultBadge, alt: __('Award badge', 'delta9-digital-blocks-plugin') }];
	}

	const panelTitle = tab === 'description'
		? flavor.description.split('\n\n')[0]
		: TABS.find((t) => t.key === tab)?.label;

	const panelBody = tab === 'description'
		? flavor.description.split('\n\n').slice(1).join('\n\n')
		: flavor[tab];

	return (
		<section
			className='yb-single-product'
			style={{
				'--page-bg': flavor.pageBg,
				'--page-contrast': flavor.pageContrast,
				'--name-color': flavor.nameColor,
			}}
		>
			<div className='yb-single-product__inner'>

				<div className='yb-single-product__hero'>

					<div className='yb-single-product__flavors'>
						{fixtureFlavors.map((f) => (
							<button
								type='button'
								key={f.id}
								className={`yb-single-product__flavorCard ${f.id === activeId ? 'is-active' : ''}`}
								style={{ '--flavor-bg': f.cardBg, '--flavor-name': f.nameColor }}
								onClick={() => setActiveId(f.id)}
							>
								<div className='yb-single-product__flavorCard__image'>
									<img src={f.image} alt='' />
								</div>
								<span className='yb-single-product__flavorCard__label'>{f.name}</span>
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
							<h3>{panelTitle}</h3>
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
									<strong>{pack.priceHtml}</strong>
									<span className='yb-single-product__mood'>{flavor.mood}</span>
								</div>
							</div>

							<div className='yb-single-product__sizePickerWrap'>
								<select
									className='yb-single-product__sizePicker'
									value={packIndex}
									onChange={(event) => {
										const index = parseInt(event.target.value, 10) || 0;
										setPackIndex(index);
										setQty(flavor.packOptions[index]?.quantity ?? 1);
									}}
									aria-label={__('Pack size', 'delta9-digital-blocks-plugin')}
								>
									{flavor.packOptions.map((opt, index) => (
										<option key={index} value={index}>{`${opt.label} — ${opt.priceHtml}`}</option>
									))}
								</select>
							</div>

							<div className='yb-single-product__buyBottom'>
								<div className='yb-single-product__qty'>
									<button type='button' onClick={() => setQty(Math.max(1, qty - 1))} aria-label={__('Decrease quantity', 'delta9-digital-blocks-plugin')}>−</button>
									<span className='yb-single-product__qtyInput'>{qty}</span>
									<button type='button' onClick={() => setQty(qty + 1)} aria-label={__('Increase quantity', 'delta9-digital-blocks-plugin')}>+</button>
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
