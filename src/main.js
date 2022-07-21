import Vue       from 'vue'
import App       from './App.vue'
import router    from './router'
import store     from './store'
import axios     from 'axios'
import VueAxios  from 'vue-axios'
import vuetify   from './plugins/vuetify'
import functions from './plugins/functions'
import userCache from './plugins/userCache'
import Ads       from 'vue-google-adsense'
import VueGtag   from 'vue-gtag'
import './styles/mhrss.scss'
import './styles/mhrssi.scss'

Vue.config.productionTip = false

Vue.use(VueAxios, axios)

Vue.use(userCache, {cacheName: process.env.VUE_APP_CACHE_NAME})

Vue.use(require('vue-script2'))
Vue.use(Ads.AutoAdsense, {adClient: 'ca-pub-8602791446931111'})
Vue.use(Ads.Adsense)

Vue.use(VueGtag, {
  config: {id: 'UA-1849676-1'},
  //appName: 'MHRise Skill Simulator',
  //pageTrackerScreenviewEnabled: true,
}, router)

Vue.mixin(functions)

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
