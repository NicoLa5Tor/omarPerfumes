const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'shop-catalog': path.resolve(
			__dirname,
			'theme/omar-perfumes/assets/src/shop-catalog.js'
		),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'theme/omar-perfumes/assets' ),
		filename: '[name].js',
		clean: false,
	},
	plugins: defaultConfig.plugins.filter(
		( plugin ) => plugin.constructor.name !== 'CopyPlugin'
	),
};
