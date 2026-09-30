import { useSelect } from '@wordpress/data';

/**
 * Published Work posts as SelectControl options.
 */
export const useWorkPostOptions = () => useSelect((select) => {
	const posts = select('core').getEntityRecords('postType', 'work', { per_page: 100, status: 'publish', orderby: 'title', order: 'asc', _fields: 'id,title' });
	return posts?.map((post) => ({ label: post.title?.rendered || `#${post.id}`, value: post.id }));
}, []);
