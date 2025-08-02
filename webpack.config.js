const path = require('path');

module.exports = (env, argv) => ({
  entry: './assets/js/frontend-scripts.js',
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'frontend.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  targets: {
                    browsers: ['defaults']
                  }
                }
              ]
            ]
          }
        }
      }
    ]
  },
  devtool: argv.mode === 'development' ? 'source-map' : false
});
