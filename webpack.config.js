
/* Imports */

const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const path = require( 'path' );

/* Output path */

let outputPath = path.resolve( __dirname, 'Formation', 'Admin', 'assets', 'public' ),
    outputCommonPath = path.resolve( __dirname, 'Formation', 'Common', 'assets', 'public' );

/* Resolve to root */

let resolve = {
  alias: {
    // Formation: path.resolve( __dirname, '../../../formation/src' ),
    Formation: '@alanizcreative/formation/src',
    Admin: path.resolve( __dirname, 'Formation', 'Admin', 'assets', 'src' )
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
    // exclude: /node_modules/,
    loader: 'babel-loader',
    options: {
      presets: [
        [
          '@babel/preset-env',
          {
            targets: { chrome: '58', ie: '11' }
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
                  'ie >= 11'
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

/* Output environment */

let outputCompatEnv = {
  arrowFunction: false,
  bigIntLiteral: false,
  const: false,
  destructuring: false,
  dynamicImport: false,
  forOf: false,
  module: false
};

/* Block paths */

let blocks = [
  'contact-form/field',
  'contact-form/form',
  'contact-form/group',
  'contact-form/group-top',
  'contact-form/group-bottom',
  'insert-block'
];

let blocksEntry = {};

blocks.forEach( b => {
  blocksEntry[b] = './Formation/Common/assets/src/blocks/' + b + '.js';
} );

/* Exports */

module.exports = [
  
  /* Admin: settings */

  {
    mode: 'production',
    entry: {
      'settings': [
        './Formation/Admin/assets/src/settings/index.js', 
        './Formation/Admin/assets/src/settings/index.scss'
      ] 
    },
    output: {
      path: outputPath,
      publicPath: '/',
      filename: 'js/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5',
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
        './Formation/Admin/assets/src/settings/tab-nav/sections.js'
      ] 
    },
    output: {
      path: outputPath,
      publicPath: '/',
      filename: 'js/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5',
  },

  /* Admin: settings business */

  {
    mode: 'production',
    entry: {
      'business': [
        './Formation/Admin/assets/src/settings/business/admin.js'
      ] 
    },
    output: {
      path: outputPath,
      publicPath: '/',
      filename: 'js/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5',
  },

  /* Common: blocks and field */

  {
    mode: 'production',
    entry: {
      'blocks': './Formation/Common/assets/src/blocks/index.scss',
      'field': [
        './Formation/Common/assets/src/field/index.js', 
        './Formation/Common/assets/src/field/index.scss'
      ]
    },
    output: {
      path: outputCommonPath,
      publicPath: '/',
      filename: 'js/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5',
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
        './Formation/Common/assets/src/field/objects/select-fields.js', 
      ] 
    },
    output: {
      path: outputCommonPath,
      publicPath: '/',
      filename: 'js/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5'
  },

  /* Common: blocks */

  {
    mode: 'production',
    entry: blocksEntry,
    output: {
      path: outputCommonPath,
      publicPath: '/',
      filename: 'js/blocks/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5'
  },

  /* Common: block utilities */

  {
    mode: 'production',
    entry: {
      'utils': './Formation/Common/assets/src/blocks/utils.js'
    },
    output: {
      library: 'blockUtils',
      path: outputCommonPath,
      publicPath: '/',
      filename: 'js/blocks/[name].js',
      environment: outputCompatEnv
    },
    module: {
      rules: rules
    },
    resolve: resolve,
    target: 'es5'
  }

];
