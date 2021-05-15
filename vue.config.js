process.env.VUE_APP_VERSION = require('./package.json').version

module.exports = {
  publicPath: process.env.NODE_ENV === 'production'
    ? `/mhr/v${process.env.npm_package_version}/`
    : '/',// '/mhr-simulator/',
  outputDir: process.env.NODE_ENV === 'production'
    ? `v${process.env.npm_package_version}`
    : 'dist',
  pages: {
    index: {
      entry: 'src/main.js',
      template: 'public/mhrss.html',
      title: 'MHRise: Skill Simulator Ver.' + process.env.npm_package_version,
    }
  },
  productionSourceMap: false,
  transpileDependencies: [
    'vuetify'
  ],
}
