const path = require('path')
const webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserPlugin = require('terser-webpack-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin')
const { VueLoaderPlugin } = require('vue-loader');

module.exports = function(env, argv) {
    return {
        target: 'web',
        mode: 'development',
        devtool: env.WEBPACK_SERVE ? 'inline-source-map' : 'source-map',
        optimization: {
            moduleIds: 'deterministic',
            splitChunks: {
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
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
                            drop_console: false,
                        },
                        mangle: true,
                        module: false,
                    },
                }),
            ],
        },
        entry: {
            app: './src/main.ts',
        },
        output: {
            path: path.resolve(__dirname, '../../public/static/'),
            publicPath: env.WEBPACK_SERVE ? 'https://meals.test:1338/static/' : '/static/',
            assetModuleFilename: 'assets/[name].[contenthash:4][ext][query]',
            clean: true,
            filename: '[name].js',
            chunkFilename: env.WEBPACK_SERVE ? '[id].js' : 'chunks/[id].js',
        },
        resolve: {
            preferRelative: true,
            extensions: ['.js', '.ts', '.json'],
            descriptionFiles: ['package.json'],
            aliasFields: ['browser', 'main'],
            modules: [
                path.resolve('./'),
                'node_modules'
            ],
            alias: {
                jquery: path.resolve('./node_modules/jquery/dist/jquery.js'),
                '@': path.resolve(__dirname, './src'),
                'vue-i18n': 'vue-i18n/dist/vue-i18n.runtime.esm-bundler.js',
                'tools': path.resolve(__dirname, './src/tools'),
            }
        },
        module: {
            rules: [
                {
                    test: /jquery\.js$/,
                    loader: 'expose-loader',
                    options: {
                        exposes: ['$', 'jQuery'],
                    },
                },
                {
                    test: /\.(js)$/,
                    exclude: [/node_modules/],
                    use: ['babel-loader'],
                },
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
                {
                    test :/\.vue$/,
                    use: [
                        'vue-loader',
                    ],
                },
                {
                    test: /\.(json5?|ya?ml)$/, // target json, json5, yaml and yml files
                    type: 'javascript/auto',
                    loader: '@intlify/vue-i18n-loader',
                    include: [ // Use `Rule.include` to specify the files of locale messages to be pre-compiled
                        path.resolve(__dirname, 'src/locales')
                    ]
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
            new VueLoaderPlugin(),
            new webpack.IgnorePlugin({
                resourceRegExp: /^\.\/locale$/,
                contextRegExp: /moment$/,
            }),
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery',
            }),
            new webpack.DefinePlugin({
                'process.browser': true,
                'process.env.MODE': argv.mode,
                'process.env.APP_BASE_URL': JSON.stringify(process.env.APP_BASE_URL),
                'process.env.MERCURE_PUBLIC_URL': process.env.MERCURE_PUBLIC_URL,
                __VUE_OPTIONS_API__: true,
                __VUE_PROD_DEVTOOLS__: true,
                __VUE_I18N_FULL_INSTALL__: true,
                __VUE_I18N_LEGACY_API__: false,
                __INTLIFY_PROD_DEVTOOLS__: false
            })
        ],
        devServer: {
            open: true,
            allowedHosts: [ 'meals.test' ],
            host: 'meals.test',
            port: 1338,
            proxy: {
                '/': {
                    target: 'http://meals.test',
                    secure: false,
                }
            },
            hot: true,
            server: 'https',
            headers: {
                'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
                'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization',
                'Access-Control-Allow-Origin': '*',
            },
            static: {
                directory: path.resolve(__dirname, '../../public'),
            }
        },
        watchOptions: {
            poll: true
        }
    }
}
