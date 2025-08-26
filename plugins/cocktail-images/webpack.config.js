const path = require('path');

module.exports = {
  entry: {
    'frontend': './src/frontend.js',
    'block-editor': './src/block-editor.js'
  },
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: '[name].js',
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      }
    ]
  },
  externals: {
    '@wordpress/element': 'wp.element',
    '@wordpress/components': 'wp.components',
    '@wordpress/block-editor': 'wp.blockEditor',
    '@wordpress/hooks': 'wp.hooks',
    '@wordpress/compose': 'wp.compose',
    '@wordpress/i18n': 'wp.i18n'
  }
};
