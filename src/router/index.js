import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Decorations from '../views/Decorations.vue'
import Talismans from '../views/Talismans.vue'
import Skills from '../views/Skills.vue'
import Loadouts from '../views/Loadouts.vue'
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
    path: '/loadouts',
    name: 'Loadouts',
    component: Loadouts,
  },
  {
    path: '/manage',
    name: 'Manage',
    component: Manage,
  },
]

const router = new VueRouter({
  mode: 'history',
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