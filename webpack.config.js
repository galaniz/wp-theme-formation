
/* Imports */

const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const path = require( 'path' );

/* Resolve to root */

let resolve = {
  alias: {
    Formation: path.resolve( __dirname, '../../formation/src' )
    // Formation: '@alanizcreative/formation/src'
  },
  extensions: [
    '.sass',
    '.scss',
    '.css',
    '.js',
    '.json',
    '.jsx'
  ]
};

/* Rules */

let rules = [
  {
    test: /\.js$/,
    exclude: /node_modules/,
    loader: 'babel-loader',
    options: {
      presets: [
        [
          '@babel/preset-env',
          {
            modules: false,
            targets: {
              browsers: [
                'last 3 versions',
                'ie >= 10'
              ]
            }
          }
        ]
      ],
      plugins: [
        [
          'transform-react-jsx',
          {
            pragma: 'wp.element.createElement'
          }
        ]
      ]
    }
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
          postcssOptions: {
            plugins: {
              'postcss-preset-env': {
                browsers: [
                  'last 3 versions',
                  'ie >= 10'
                ]
              },
              'cssnano': {},
              'postcss-combine-duplicated-selectors': {}
            },
          },
        }
      },
      {
        loader: 'sass-loader',
        options: {
          implementation: require( 'sass' )
        }
      }
    ]
  }
];

/* Block paths */

let blocks = [
  'contact-form/field',
  'contact-form/form',
  'contact-form/group',
  'contact-form/group-top',
  'contact-form/group-bottom',
  'extend-media/attr',
  'extend-media/control',
  'insert-block',
  'media'
];

let blocksEntry = {};

blocks.forEach( ( b ) => {
  blocksEntry[b] = __dirname + '/Formation/Common/assets/src/blocks/' + b + '.js';
} );

/* Exports */

module.exports = [
  
  /* Admin: settings */

  {
    mode: 'production',
    entry: {
      'settings': [
        __dirname + '/Formation/Admin/assets/src/settings/index.js', 
        __dirname + '/Formation/Admin/assets/src/settings/index.scss'
      ] 
    },
    output: {
      path: __dirname + '/Formation/Admin/assets/public/',
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
      } )
    ]
  },
  
  /* Admin: settings tab nav */

  {
    mode: 'production',
    entry: {
      'tab-nav': [
        __dirname + '/Formation/Admin/assets/src/settings/tab-nav/sections.js'
      ] 
    },
    output: {
      path: __dirname + '/Formation/Admin/assets/public/',
      publicPath: '/',
      filename: 'js/[name].js'
    },
    module: {
      rules: rules
    },
    resolve: resolve
  },

  /* Admin: settings business */

  {
    mode: 'production',
    entry: {
      'tab-nav': [
        __dirname + '/Formation/Admin/assets/src/settings/business/admin.js'
      ] 
    },
    output: {
      path: __dirname + '/Formation/Admin/assets/public/',
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
      'blocks': __dirname + '/Formation/Common/assets/src/blocks/index.scss',
      'field': [
        __dirname + '/Formation/Common/assets/src/field/index.js', 
        __dirname + '/Formation/Common/assets/src/field/index.scss'
      ]
    },
    output: {
      path: __dirname + '/Formation/Common/assets/public/',
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
      } )
    ]
  }, 

  /* Common: select fields */

  {
    mode: 'production',
    entry: {
      'select-fields': [
        __dirname + '/Formation/Common/assets/src/field/objects/select-fields.js', 
      ] 
    },
    output: {
      path: __dirname + '/Formation/Common/assets/public/',
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
    entry: blocksEntry,
    output: {
      path: __dirname + '/Formation/Common/assets/public/',
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
      'utils': __dirname + '/Formation/Common/assets/src/blocks/utils.js'
    },
    output: {
      library: 'blockUtils',
      path: __dirname + '/Formation/Common/assets/public/',
      publicPath: '/',
      filename: 'js/blocks/[name].js'
    },
    module: {
      rules: rules
    },
    resolve: resolve
  }

];
