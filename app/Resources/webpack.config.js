const path = require('path')
const webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserPlugin = require('terser-webpack-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin')

module.exports = function(env) {
    return {
        target: 'web',
        mode: 'development',
        devtool: env.WEBPACK_SERVE ? 'inline-source-map' : 'source-map',
        optimization: {
            moduleIds: 'deterministic',
            splitChunks: {
                cacheGroups: {
                    vendor: {
                        test: /[\\/]bower_components[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                    },
                },
            },
            minimize: !env.WEBPACK_SERVE,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        ecma: 5,
                        compress: {
                            drop_console: true,
                        },
                        mangle: true,
                        module: false,
                    },
                }),
            ],
        },
        entry: {
            app: './js/init.js',
        },
        output: {
            path: path.resolve(__dirname, '../../web/static/'),
            publicPath: env.WEBPACK_SERVE ? 'https://localhost:1337/' : '/',
            assetModuleFilename: 'assets/[name].[contenthash:4][ext][query]',
            clean: true,
            filename: '[name].js',
            chunkFilename: env.WEBPACK_SERVE ? '[id].js' : 'chunks/[id].js',
        },
        resolve: {
            preferRelative: true,
            extensions: ['.js', '.json'],
            descriptionFiles: ['package.json', 'bower.json'],
            aliasFields: ['browser', 'main'],
            modules: [
                path.resolve('./'),
                'bower_components',
                'node_modules'
            ],
        },
        module: {
            rules: [
                {
                    test: /\.(js)$/,
                    exclude: [/node_modules/],
                    use: ['babel-loader'],
                },
                {
                    test: /\.(png|jpe?g|gif|svg|eot|ttf|woff|woff2|otf)(\?v=[0-9]\.[0-9]\.[0-9])?$/i,
                    // More information here https://webpack.js.org/guides/asset-modules/
                    type: 'asset/resource',
                },
                {
                    test: /\.css$/i,
                    use: [
                        env.WEBPACK_SERVE ? 'style-loader' : MiniCssExtractPlugin.loader,
                        'css-loader',
                        'postcss-loader',
                    ],
                },
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        // Creates `style` nodes from JS strings
                        env.WEBPACK_SERVE ? 'style-loader' : MiniCssExtractPlugin.loader,
                        // Translates CSS into CommonJS
                        'css-loader',
                        // Run PostCSS
                        'postcss-loader',
                        // Compiles Sass to CSS
                        {
                            loader: 'sass-loader',
                            options: {
                                sassOptions: {
                                    indentWidth: 4,
                                },
                            },
                        },
                    ],
                },
                {
                    // Load .html files as string
                    test: /\.html$/i,
                    use: ['html-loader'],
                },
            ],
        },
        plugins: [
            new WebpackManifestPlugin(),
            new MiniCssExtractPlugin(),
            new webpack.BannerPlugin({
                banner: 'name:[name], file:[file], fullhash:[fullhash], chunkhash:[chunkhash]',
            }),
            new webpack.IgnorePlugin({
                resourceRegExp: /^\.\/locale$/,
                contextRegExp: /moment$/,
            }),
        ],
        devServer: {
            contentBase: path.resolve(__dirname, 'public'),
            host: '0.0.0.0',
            port: 1337,
            sockHost: 'localhost',
            hot: true,
            overlay: true,
            disableHostCheck: true,
            https: true,
            headers: {
                'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
                'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization',
                'Access-Control-Allow-Origin': '*',
            },
        },
    }
}

// console.log('config:', module.exports({}).devServer)
// process.exit(0)
// console.log(module.exports)
// console.log('/config')
