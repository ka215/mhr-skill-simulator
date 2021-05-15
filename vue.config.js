process.env.VUE_APP_VERSION = require('./package.json').version
process.env.VUE_APP_SUBDIR  = 'mhr/'

module.exports = {
  publicPath: process.env.NODE_ENV === 'production'
    ? `/${process.env.VUE_APP_SUBDIR}v${process.env.VUE_APP_VERSION}/`
    : '/',
  outputDir: process.env.NODE_ENV === 'production'
    ? `v${process.env.VUE_APP_VERSION}`
    : 'dist',
  pages: {
    index: {
      entry: 'src/main.js',
      template: 'public/mhrss.html',
      title: `MHRise: Skill Simulator Ver.${process.env.VUE_APP_VERSION}`,
    }
  },
  productionSourceMap: false,
  transpileDependencies: [
    'vuetify'
  ],
}
