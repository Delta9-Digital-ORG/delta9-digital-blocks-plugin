import React from 'react';
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { checkAttr, getAttrKey, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { useParentVariant } from './use-parent-variant';

export const BadgeCardEditor = ({ attributes, setAttributes, clientId }) => {
	const { blockClass } = attributes;
	const variant = useParentVariant(clientId);

	const accent = checkAttr('badgeCardAccent', attributes, manifest);
	const text = (key) => checkAttr(key, attributes, manifest);
	const setText = (key) => (value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<article
			className={classnames(blockClass, 'd9-bcard')}
			style={accent ? { '--d9-bcard-accent': `var(--wp--preset--color--${accent})` } : undefined}
		>
			<RichText
				tagName='span'
				className='d9-bcard__badge'
				placeholder='01'
				value={text('badgeCardBadge')}
				onChange={setText('badgeCardBadge')}
				allowedFormats={[]}
			/>
			<div className='d9-bcard__body'>
				<span className='d9-bcard__cut' aria-hidden='true' />
				<span className='d9-bcard__fx' aria-hidden='true' />
				<span className='d9-bcard__fy' aria-hidden='true' />
				<RichText
					tagName='h3'
					className='d9-bcard__title'
					placeholder={__('Card title', 'delta9-digital-blocks-plugin')}
					value={text('badgeCardTitle')}
					onChange={setText('badgeCardTitle')}
					allowedFormats={[]}
				/>
				<RichText
					tagName='p'
					className='d9-bcard__text'
					placeholder={__('Short supporting copy', 'delta9-digital-blocks-plugin')}
					value={text('badgeCardBody')}
					onChange={setText('badgeCardBody')}
					allowedFormats={['core/bold', 'core/italic', 'core/link']}
				/>
				<footer className='d9-bcard__foot'>
					{variant === 'steps' ? (
						<>
							<RichText
								tagName='span'
								className='d9-bcard__step'
								placeholder={__('Step 1 of 4', 'delta9-digital-blocks-plugin')}
								value={text('badgeCardStepLabel')}
								onChange={setText('badgeCardStepLabel')}
								allowedFormats={[]}
							/>
							<RichText
								tagName='span'
								className='d9-bcard__when'
								placeholder={__('Week 0', 'delta9-digital-blocks-plugin')}
								value={text('badgeCardWhenLabel')}
								onChange={setText('badgeCardWhenLabel')}
								allowedFormats={[]}
							/>
						</>
					) : (
						<RichText
							tagName='span'
							className='d9-bcard__link'
							placeholder={__('Learn more →', 'delta9-digital-blocks-plugin')}
							value={text('badgeCardLinkLabel')}
							onChange={setText('badgeCardLinkLabel')}
							allowedFormats={[]}
						/>
					)}
				</footer>
			</div>
		</article>
	);
};
