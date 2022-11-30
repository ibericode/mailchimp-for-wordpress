const path = require('path')

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
  }
}
