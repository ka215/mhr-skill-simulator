import Vue       from 'vue'
import App       from './App.vue'
import router    from './router'
import store     from './store'
import axios     from 'axios'
import VueAxios  from 'vue-axios'
import vuetify   from './plugins/vuetify'
import functions from './plugins/functions'
import './styles/mhrss.scss'
import './styles/mhrssi.scss'

Vue.config.productionTip = false

Vue.use(VueAxios, axios)

Vue.mixin(functions)

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
