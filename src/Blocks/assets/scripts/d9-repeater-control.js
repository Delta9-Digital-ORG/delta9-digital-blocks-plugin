import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Button, SelectControl, TextControl, TextareaControl } from '@wordpress/components';

export const D9_ACCENTS = [
	{ label: __('Mint', 'delta9-digital-blocks-plugin'), value: 'mint' },
	{ label: __('Coral', 'delta9-digital-blocks-plugin'), value: 'coral' },
	{ label: __('Cyan', 'delta9-digital-blocks-plugin'), value: 'cyan' },
	{ label: __('Yellow', 'delta9-digital-blocks-plugin'), value: 'yellow' },
	{ label: __('Light', 'delta9-digital-blocks-plugin'), value: 'light' },
];

/**
 * Inspector repeater for array-of-object attributes (rows, cards).
 *
 * fields: [{ key, label, type: 'text' | 'textarea' | 'accent', help? }]
 */
export const D9RepeaterControl = ({ label, value, onChange, fields, itemLabel }) => {
	const rows = Array.isArray(value) ? value : [];
	const blank = () => Object.fromEntries(fields.map((f) => [f.key, '']));
	const update = (i, patch) => onChange(rows.map((row, n) => (n === i ? { ...row, ...patch } : row)));
	const move = (i, d) => {
		const next = [...rows];
		const j = i + d;
		if (j < 0 || j >= next.length) {
			return;
		}
		[next[i], next[j]] = [next[j], next[i]];
		onChange(next);
	};

	return (
		<div className='d9-repeater'>
			{label && <p style={{ fontWeight: 600, marginBottom: 8 }}>{label}</p>}
			{rows.map((row, i) => (
				<div key={i} style={{ borderTop: '1px solid #ddd', padding: '12px 0' }}>
					{/* translators: 1: item label, 2: number. */}
					<strong>{sprintf('%1$s %2$d', itemLabel || __('Item', 'delta9-digital-blocks-plugin'), i + 1)}</strong>
					{fields.map((f) => {
						const v = row[f.key] ?? '';
						const set = (nv) => update(i, { [f.key]: nv });
						if (f.type === 'textarea') {
							return <TextareaControl key={f.key} label={f.label} help={f.help} value={v} onChange={set} />;
						}
						if (f.type === 'accent') {
							return (
								<SelectControl
									key={f.key}
									label={f.label}
									value={v}
									options={[{ label: __('From the rotation', 'delta9-digital-blocks-plugin'), value: '' }, ...D9_ACCENTS]}
									onChange={set}
								/>
							);
						}
						return <TextControl key={f.key} label={f.label} help={f.help} value={v} onChange={set} />;
					})}
					<div style={{ display: 'flex', gap: 8 }}>
						<Button variant='tertiary' disabled={i === 0} onClick={() => move(i, -1)}>{__('Up', 'delta9-digital-blocks-plugin')}</Button>
						<Button variant='tertiary' disabled={i === rows.length - 1} onClick={() => move(i, 1)}>{__('Down', 'delta9-digital-blocks-plugin')}</Button>
						<Button variant='link' isDestructive onClick={() => onChange(rows.filter((_, n) => n !== i))}>{__('Remove', 'delta9-digital-blocks-plugin')}</Button>
					</div>
				</div>
			))}
			<Button variant='secondary' onClick={() => onChange([...rows, blank()])}>
				{sprintf(__('Add %s', 'delta9-digital-blocks-plugin'), (itemLabel || __('item', 'delta9-digital-blocks-plugin')).toLowerCase())}
			</Button>
		</div>
	);
};

export const D9_ROW_FIELDS = [
	{ key: 'title', label: __('Title', 'delta9-digital-blocks-plugin') },
	{ key: 'body', label: __('Text', 'delta9-digital-blocks-plugin'), type: 'textarea' },
	{ key: 'accent', label: __('Accent', 'delta9-digital-blocks-plugin'), type: 'accent' },
];
