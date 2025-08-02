const path = require('path');

module.exports = {
  entry: './assets/js/frontend-scripts.js',
  output: {
    filename: 'frontend.js',
    path: path.resolve(__dirname, 'build')
  }
};
