
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const Fiber = require( 'fibers' );

let rules = [
    {
        test: /\.js$/,
        exclude: /node_modules/,
        loaders: 'babel-loader'
    },
    {
        test: /\.(css|sass|scss)$/,
        use: [
            {
                loader: MiniCssExtractPlugin.loader
            },
            {
                loader: 'css-loader',
                options: { 
                    url: false,
                    importLoaders: 1
                }
            },
            {
                loader: 'postcss-loader',
                options: {
                    ident: 'postcss',
                    plugins: [
                        require( 'autoprefixer' )( {} ),
                        require( 'cssnano' )( { preset: 'default' } ),
                        require( 'css-mqpacker' ),
                        require( 'postcss-combine-duplicated-selectors' ) 
                    ],
                    minimize: true
                }
            },
            {
                loader: 'sass-loader',
                options: {
                    implementation: require( 'sass' ),
                    fiber: Fiber
                }
            }
        ]
    }
];

module.exports = [
    {
        mode: 'production',
        entry: {
            'field': [
                __dirname + '/src/common/assets/src/field/index.js', 
                __dirname + '/src/common/assets/src/field/index.scss'
            ]
        },
        output: {
            path: __dirname + '/src/common/assets/public/',
            publicPath: '/',
            filename: 'js/[name].js'
        },
        module: {
            rules: rules
        },
        plugins: [
            new MiniCssExtractPlugin( {
                filename: 'css/[name].css'
            } ),
            new OptimizeCssAssetsPlugin()
        ]
    }, 
    {
        mode: 'production',
        entry: {
            'select-fields': [
                __dirname + '/src/common/assets/src/field/objects/select-fields.js', 
            ]
        },
        output: {
            path: __dirname + '/src/common/assets/public/',
            publicPath: '/',
            filename: 'js/[name].js'
        },
        module: {
            rules: rules
        }
    }
];
