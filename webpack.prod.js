const { merge }               = require( 'webpack-merge' );
const webpackCommon           = require( './webpack.common.js' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const MiniCssExtractPlugin    = require( 'mini-css-extract-plugin' );

module.exports = merge( webpackCommon, {
	mode: 'production',
	module: {
		rules: [
			{
				test: [/.css$|.scss$/],
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions: {
								plugins: [
									require( 'autoprefixer' )
								],
							},
						}
					},
					'sass-loader'
				]
			},
		]
	},
	plugins: [
		new OptimizeCSSAssetsPlugin(),
		new MiniCssExtractPlugin( {
			filename: '[name].css'
		} ),
	]
} );
