/**
 * This is a main entrypoint for Webpack config.
 * All the settings are pulled from node_modules/@eightshift/frontend-libs/webpack.
 * We are loading mostly used configuration but you can always override or turn off the default setup and provide your own.
 * Please referer to Eightshift-libs wiki for details.
 */
 module.exports = (env, argv) => {

	const projectConfig = {
		config: {
			projectDir: __dirname, // Current project directory absolute path.
			projectUrl: 'dev.boilerplate.com', // Used for providing browsersync functionality.
			projectPath: 'wp-content/plugins/delta9-digital-blocks-plugin', // Project path relative to project root.
		},
	};

	// Generate webpack config for this project using options object.
	const config = require('./node_modules/@eightshift/frontend-libs/webpack')(argv.mode, projectConfig);

	// frontend-libs only has asset rules for images/fonts. Emit 3D models
	// (the single-product can) as copied assets so `import url from './x.glb'`
	// resolves to a real URL for GLTFLoader.
	config.module.rules.push({
		test: /\.(glb|gltf)$/i,
		type: 'asset/resource',
		generator: { filename: '[name].[contenthash][ext]' },
	});

	return config;
};