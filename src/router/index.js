import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Skills from '../views/Skills.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/skills',
    name: 'Skills',
    component: Skills,
  },
]

const router = new VueRouter({
  mode: 'history',
  //base: /^(localhost|127\.0\.0\.1)$/.test(document.location.hostname) ? 'mhr-simulator/': 'mhr/v0.1.3/',
  base: process.env.BASE_URL,
  routes,
  scrollBehavior(to, from, savedPosition){
    if (savedPosition) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve(savedPosition)
        })
      })
    } else {
      return {x:0, y:0}
    }
  },
})

export default router