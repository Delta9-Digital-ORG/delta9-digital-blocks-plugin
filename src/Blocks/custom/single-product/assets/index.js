// View code runs as an inline ES module emitted by single-product.php so it
// can `import { store } from '@wordpress/interactivity'` against WordPress's
// real script module — webpack's bundle would have externalized it to
// `wp.interactivity`, which is undefined under the new module-based iAPI.
//
// Left intentionally empty.
