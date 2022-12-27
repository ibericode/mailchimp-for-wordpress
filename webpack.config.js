const path = require('path')
const CopyPlugin = require('copy-webpack-plugin');
const css = require('lightningcss');

module.exports = {
  mode: process.env.NODE_ENV === 'development' ? 'development' : 'production',
  entry: {
    admin: './assets/src/js/admin.js',
    'integrations-admin': './assets/src/js/integrations-admin.js',
    forms: './assets/src/js/forms.js',
    'forms-submitted': './assets/src/js/forms-submitted.js',
    'forms-admin': './assets/src/js/forms-admin.js',
    'forms-block': './assets/src/js/forms-block.js'
  },
  output: {
    path: path.resolve(__dirname, 'assets/js'),
    filename: '[name].js'
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: ['@babel/plugin-transform-react-jsx']
          }
        }
      }
    ]
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        { from: './assets/src/img', to: path.resolve(__dirname, './assets/img') },
        {
          from: './assets/src/css',
          to: path.resolve(__dirname, './assets/css'),
          transform: (content, path) => {
            let {code, map} = css.transform({
              filename: path.split('/').pop(),
              code: Buffer.from(content),
              minify: true,
              sourceMap: false,
            });
            return code;
          },
        },
      ],
    }),
  ],
}
