import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Decorations from '../views/Decorations.vue'
import Talismans from '../views/Talismans.vue'
import Skills from '../views/Skills.vue'
import Manage from '../views/Manage.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  /*
  {
    path: '/weapons',
    name: 'Weapons',
    component: Weapons,
  },
  {
    path: '/armors',
    name: 'Armors',
    component: Armors,
  },
  */
  {
    path: '/decorations',
    name: 'Decorations',
    component: Decorations,
  },
  {
    path: '/talismans',
    name: 'Talismans',
    component: Talismans,
  },
  {
    path: '/skills',
    name: 'Skills',
    component: Skills,
  },
  {
    path: '/manage',
    name: 'Manage',
    component: Manage,
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