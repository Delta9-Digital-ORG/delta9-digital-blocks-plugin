import { useSelect } from '@wordpress/data';

/**
 * Footer variant of the parent Badge Cards grid ('link' | 'steps').
 */
export const useParentVariant = (clientId) => useSelect((select) => {
	const { getBlockParentsByBlockName, getBlockAttributes } = select('core/block-editor');
	const [parentId] = getBlockParentsByBlockName(clientId, 'eightshift-boilerplate/badge-cards', true);
	return parentId ? (getBlockAttributes(parentId)?.badgeCardsVariant ?? 'link') : 'link';
}, [clientId]);
