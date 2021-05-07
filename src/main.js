import Vue     from 'vue'
import App     from './App.vue'
import router  from './router'
import vuetify from './plugins/vuetify'
import functions from './plugins/functions'
import './styles/mhrss.scss'
import './styles/mhrssi.scss'

Vue.config.productionTip = false

Vue.mixin(functions)

new Vue({
  router,
  vuetify,
  render: h => h(App)
}).$mount('#app')
