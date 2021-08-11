const { merge }       = require( 'webpack-merge' );
const webpackCommon   = require( './webpack.common.js' );
const webpack         = require( 'webpack' );
const chokidar        = require( 'chokidar' );
const config          = require( './development/merge-configs.js' );
const protocol        = config.secure ? 'https' : 'http';
const StyleLintPlugin = require( 'stylelint-webpack-plugin' );

module.exports = merge( webpackCommon, {
	mode: 'development',
	devtool: 'source-map',
	output: {
		publicPath: `${protocol}://localhost:${config.port}/src/`
	},
	devServer: {
		headers: { 'Access-Control-Allow-Origin': '*' },
		hot: true,
		clientLogLevel: 'warning',
		port: config.port,
		https: config.secure,
		overlay: {
			warnings: false,
			errors: true
		},
		proxy: {
			'*': {
				target: config.url,
				secure: config.secure,
				changeOrigin: true
			}
		},
		disableHostCheck: true,
		before ( app, server ) {

			// Refresh the browser when a .php or .twig file changes
			const files = [
				'./templates/**/*.twig',
				'./app/**/*.php'
			];

			chokidar.watch( files, {
				alwaysStat: true,
				atomic: false,
				followSymlinks: false,
				ignoreInitial: true,
				ignorePermissionErrors: true,
				persistent: true,
				usePolling: true
			} ).on( 'all', () => {
				server.sockWrite( server.sockets, 'content-changed' );
			} );
		},
	},
	module: {
		rules: [
			{
				test: [/.css$|.scss$/],
				use: [
					{
						loader: 'style-loader'
					},
					{
						loader: 'css-loader',
						options: {
							sourceMap: true,
						}
					},
					{
						loader: 'postcss-loader',
						options: {
							sourceMap: true,
							postcssOptions: {
								plugins: [
									require( 'autoprefixer' )
								],
							},
						}
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: true
						}
					}
				]
			},
		],
	},
	plugins: [
		new webpack.NamedModulesPlugin(),
		new webpack.HotModuleReplacementPlugin(),
		new StyleLintPlugin(),
	]
} );
