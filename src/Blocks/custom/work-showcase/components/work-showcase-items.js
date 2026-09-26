import React from 'react';
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, TextControl, SelectControl, ToggleControl, Card, CardBody } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

const MAX_ITEMS = 8;

/** Map a WP media object to a tile item. */
const toItem = (media) => ({
	mediaId: media.id,
	mediaUrl: media.url,
	alt: media.alt || '',
	type: 'can',
	caption: media.caption || '',
	link: '',
	blur: undefined,
});

export const WorkShowcaseItems = ({ attributes, setAttributes }) => {
	const items = checkAttr('workShowcaseItems', attributes, manifest) || [];
	const key = getAttrKey('workShowcaseItems', attributes, manifest);
	const setItems = (next) => setAttributes({ [key]: next });

	const update = (index, patch) => {
		const next = items.map((it, i) => (i === index ? { ...it, ...patch } : it));
		setItems(next);
	};

	const remove = (index) => setItems(items.filter((_, i) => i !== index));

	const move = (index, delta) => {
		const target = index + delta;
		if (target < 0 || target >= items.length) {
			return;
		}
		const next = [...items];
		[next[index], next[target]] = [next[target], next[index]];
		setItems(next);
	};

	const onSelect = (media) => {
		const list = Array.isArray(media) ? media : [media];
		const mapped = list.map(toItem);
		setItems([...items, ...mapped].slice(0, MAX_ITEMS));
	};

	return (
		<div className="d9-showcase-items">
			<MediaUploadCheck>
				<MediaUpload
					multiple
					gallery
					allowedTypes={['image']}
					value={items.map((it) => it.mediaId).filter(Boolean)}
					onSelect={onSelect}
					render={({ open }) => (
						<Button variant="secondary" onClick={open} disabled={items.length >= MAX_ITEMS}>
							{items.length ? __('Add / edit tiles', 'delta9-digital-blocks-plugin') : __('Select tiles', 'delta9-digital-blocks-plugin')}
						</Button>
					)}
				/>
			</MediaUploadCheck>

			{items.map((item, index) => (
				<Card key={`${item.mediaId}-${index}`} size="small" style={{ marginTop: '0.75rem' }}>
					<CardBody>
						<div style={{ display: 'flex', gap: '0.75rem', alignItems: 'flex-start' }}>
							{item.mediaUrl ? (
								<img
									src={item.mediaUrl}
									alt=""
									style={{ width: 48, height: 48, objectFit: 'cover', borderRadius: 4, flex: '0 0 auto' }}
								/>
							) : null}
							<div style={{ flex: '1 1 auto' }}>
								<SelectControl
									label={__('Type', 'delta9-digital-blocks-plugin')}
									value={item.type || 'can'}
									options={manifest.options.workShowcaseItemType}
									onChange={(v) => update(index, { type: v })}
								/>
								<TextControl
									label={__('Caption', 'delta9-digital-blocks-plugin')}
									value={item.caption || ''}
									onChange={(v) => update(index, { caption: v })}
								/>
								<TextControl
									label={__('Link', 'delta9-digital-blocks-plugin')}
									type="url"
									value={item.link || ''}
									onChange={(v) => update(index, { link: v })}
								/>
								<TextControl
									label={__('Alt text', 'delta9-digital-blocks-plugin')}
									value={item.alt || ''}
									onChange={(v) => update(index, { alt: v })}
								/>
								<ToggleControl
									label={__('Blur override', 'delta9-digital-blocks-plugin')}
									checked={item.blur === true}
									onChange={(v) => update(index, { blur: v ? true : undefined })}
								/>
							</div>
						</div>

						<div style={{ display: 'flex', gap: '0.25rem', marginTop: '0.5rem' }}>
							<Button size="small" icon="arrow-up-alt2" label={__('Move up', 'delta9-digital-blocks-plugin')} onClick={() => move(index, -1)} disabled={index === 0} />
							<Button size="small" icon="arrow-down-alt2" label={__('Move down', 'delta9-digital-blocks-plugin')} onClick={() => move(index, 1)} disabled={index === items.length - 1} />
							<Button size="small" isDestructive icon="trash" label={__('Remove', 'delta9-digital-blocks-plugin')} onClick={() => remove(index)} />
						</div>
					</CardBody>
				</Card>
			))}
		</div>
	);
};
