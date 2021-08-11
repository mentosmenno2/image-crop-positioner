const path                   = require( 'path' );
const webpack                = require( 'webpack' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const entries                = require( './development/config.entries.js' );

module.exports = {
	entry: entries,
	output: {
		path: path.resolve( process.cwd(), 'dist' ),
		filename: '[name].js'
	},
	externals: {
		'jquery': 'jQuery'
	},
	resolve: {
		modules: [
			'node_modules',
			path.resolve( __dirname, 'src' )
		],
		extensions: ['.js'],
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				enforce: 'pre',
				exclude: /(node_modules)/,
				use: [
					{
						loader: 'babel-loader',
						options: {
							presets: ['@wordpress/default'],
							plugins: ['@babel/transform-react-jsx']
						}
					},
					{
						loader: 'eslint-loader'
					}
				]
			},
			{
				test: /\.(png|jpg|gif|svg)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name].[ext]',
							outputPath: 'images/'
						}
					}
				]
			},
			{
				test: /\.(woff(2)?|ttf|eot)(\?v=\d+\.\d+\.\d+)?$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name].[ext]',
							outputPath: 'fonts/'
						}
					}
				]
			}
		]
	},
	plugins: [
		new webpack.ProvidePlugin( {
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery'
		} ),
		new CleanWebpackPlugin()
	]
};
