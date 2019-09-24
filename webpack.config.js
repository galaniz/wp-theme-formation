
/* Imports */

const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const Fiber = require( 'fibers' );
const path = require( 'path' );

/* Resolve to root */

let resolve = {
    alias: {
        Formation: path.resolve( '../formation/src/' )
    }
};

/* Rules */

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

/* Exports */

module.exports = [
    
    /* Admin: settings */

    {
        mode: 'production',
        entry: {
            'settings': [
                __dirname + '/src/admin/assets/src/settings/index.js', 
            ] 
        },
        output: {
            path: __dirname + '/src/admin/assets/public/',
            publicPath: '/',
            filename: 'js/[name].js'
        },
        module: {
            rules: rules
        },
        resolve: resolve
    },

    /* Common: blocks and field */

    {
        mode: 'production',
        entry: {
            'blocks': __dirname + '/src/common/assets/src/blocks/index.scss',
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
        resolve: resolve,
        plugins: [
            new MiniCssExtractPlugin( {
                filename: 'css/[name].css'
            } ),
            new OptimizeCssAssetsPlugin()
        ]
    }, 

    /* Common: select fields */

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
        },
        resolve: resolve
    },

    /* Common: blocks */

    {
        mode: 'production',
        entry: {
            'contact-form-field': __dirname + '/src/common/assets/src/blocks/contact-form-field.js',
            'contact-form': __dirname + '/src/common/assets/src/blocks/contact-form.js',
            'media': __dirname + '/src/common/assets/src/blocks/media.js',
            'insert-block': __dirname + '/src/common/assets/src/blocks/insert-block.js'
        },
        output: {
            path: __dirname + '/src/common/assets/public/',
            publicPath: '/',
            filename: 'js/blocks/[name].js'
        },
        module: {
            rules: rules
        },
        resolve: resolve
    },

    /* Common: block utilities */

    {
        mode: 'production',
        entry: {
            'utils': __dirname + '/src/common/assets/src/blocks/utils.js'
        },
        output: {
            library: 'blockUtils',
            path: __dirname + '/src/common/assets/public/',
            publicPath: '/',
            filename: 'js/blocks/[name].js'
        },
        module: {
            rules: rules
        },
        resolve: resolve
    }

];
