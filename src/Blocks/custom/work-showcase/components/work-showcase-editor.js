import React from 'react';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls, MediaPlaceholder } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { slots, projectSlot } from '../assets/scene/layout.js';

const MAX_ITEMS = 8;

/** Static (no-WebGL) preview mirroring the PHP fallback layout. */
const StaticLayout = ({ items, background, radius, minHeight }) => (
	<div className="d9-showcase" style={{ '--d9-bg': background, '--d9-min-h': `${minHeight}vh`, '--d9-radius': radius }}>
		<div className="d9-showcase__stage">
			<ul className="d9-showcase__tiles">
				{items.map((item, i) => {
					const slot = slots.desktop[i];
					if (!slot) {
						return null;
					}
					const p = projectSlot(slot);
					return (
						<li
							key={`${item.mediaId}-${i}`}
							className="d9-showcase__tile"
							style={{ '--dx': p.x, '--dy': p.y, '--dw': p.w, '--dh': p.h }}
						>
							{item.mediaUrl ? <img className="d9-showcase__img" src={item.mediaUrl} alt={item.alt || ''} /> : null}
						</li>
					);
				})}
			</ul>
		</div>
	</div>
);

/** Live WebGL preview: mounts the same Scene class used on the front end. */
const LivePreview = ({ items, background, radius, minHeight }) => {
	const stageRef = useRef(null);

	useEffect(() => {
		let scene;
		let cancelled = false;
		const stage = stageRef.current;
		if (!stage) {
			return undefined;
		}

		const tiles = items
			.filter((it) => it.mediaUrl)
			.map((it) => ({
				src: it.mediaUrl,
				mediumSrc: it.mediaUrl,
				aspect: 0,
				blurred: it.blur === true,
				type: it.type || 'can',
				link: '',
				caption: it.caption || '',
				alt: it.alt || '',
			}));

		(async () => {
			const { Scene } = await import('../assets/scene/Scene.js');
			if (cancelled) {
				return;
			}
			// scrollExit forced off: ScrollTrigger would be measuring the iframe.
			scene = new Scene({ container: stage, tiles, scrollExit: false, radius });
			await scene.mount();
		})();

		return () => {
			cancelled = true;
			scene?.dispose();
		};
	}, [items, radius]);

	return (
		<div className="d9-showcase" style={{ '--d9-bg': background, '--d9-min-h': `${minHeight}vh`, '--d9-radius': radius }}>
			<div className="d9-showcase__stage" ref={stageRef} />
		</div>
	);
};

export const WorkShowcaseEditor = ({ attributes, setAttributes }) => {
	const [live, setLive] = useState(false);

	const items = checkAttr('workShowcaseItems', attributes, manifest) || [];
	const eyebrow = checkAttr('workShowcaseEyebrow', attributes, manifest);
	const heading = checkAttr('workShowcaseHeading', attributes, manifest);
	const lead = checkAttr('workShowcaseLead', attributes, manifest);
	const background = checkAttr('workShowcaseBackground', attributes, manifest);
	const tileRadius = checkAttr('workShowcaseTileRadius', attributes, manifest);
	const minHeight = checkAttr('workShowcaseMinHeight', attributes, manifest);

	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	const blockProps = useBlockProps();

	const onSelectImages = (media) => {
		const list = Array.isArray(media) ? media : [media];
		const mapped = list.slice(0, MAX_ITEMS).map((m) => ({
			mediaId: m.id,
			mediaUrl: m.url,
			alt: m.alt || '',
			type: 'can',
			caption: m.caption || '',
			link: '',
			blur: undefined,
		}));
		setAttr('workShowcaseItems', mapped);
	};

	if (!items.length) {
		return (
			<div {...blockProps}>
				<MediaPlaceholder
					multiple
					allowedTypes={['image']}
					labels={{
						title: __('Work Showcase', 'delta9-digital-blocks-plugin'),
						instructions: __('Select up to eight images for the 3D showcase.', 'delta9-digital-blocks-plugin'),
					}}
					onSelect={onSelectImages}
				/>
			</div>
		);
	}

	return (
		<div {...blockProps}>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="visibility"
						isPressed={live}
						label={live ? __('Show static layout', 'delta9-digital-blocks-plugin') : __('Preview 3D scene', 'delta9-digital-blocks-plugin')}
						onClick={() => setLive((v) => !v)}
					/>
				</ToolbarGroup>
			</BlockControls>

			<div style={{ position: 'relative' }}>
				{live ? (
					<LivePreview items={items} background={background} radius={tileRadius} minHeight={minHeight} />
				) : (
					<StaticLayout items={items} background={background} radius={tileRadius} minHeight={minHeight} />
				)}

				<div className="d9-showcase__copy" style={{ position: 'relative', zIndex: 2, pointerEvents: 'auto' }}>
					<RichText
						tagName="p"
						className="d9-showcase__eyebrow"
						placeholder={__('Eyebrow', 'delta9-digital-blocks-plugin')}
						value={eyebrow}
						onChange={(v) => setAttr('workShowcaseEyebrow', v)}
						allowedFormats={['core/bold', 'core/italic']}
					/>
					<RichText
						tagName="h2"
						className="d9-showcase__heading"
						placeholder={__('Heading', 'delta9-digital-blocks-plugin')}
						value={heading}
						onChange={(v) => setAttr('workShowcaseHeading', v)}
						allowedFormats={['core/bold', 'core/italic']}
					/>
					<RichText
						tagName="p"
						className="d9-showcase__lead"
						placeholder={__('Lead paragraph', 'delta9-digital-blocks-plugin')}
						value={lead}
						onChange={(v) => setAttr('workShowcaseLead', v)}
						allowedFormats={['core/bold', 'core/italic', 'core/link']}
					/>
				</div>
			</div>
		</div>
	);
};
