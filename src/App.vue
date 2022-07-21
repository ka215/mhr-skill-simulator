<template>
  <v-app>
    <v-navigation-drawer
      v-model="drawer"
      app
      temporary
    >
      <SideMenu />
    </v-navigation-drawer>

    <v-app-bar
      ref="appBar"
      app
      class="align-center"
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-toolbar-title class="pl-0 pl-sm-1">{{ labels.title }}</v-toolbar-title>
      <v-spacer />
      <div class="d-flex my-a">
        <v-switch
          v-model="darkTheme"
          color="amber accent-4"
          class="align-center"
          dense
          hide-details
        >
          <template v-slot:label>
            <v-icon
              :color="`${darkTheme ? 'amber': 'red'} accent-2`"
            >mdi-theme-light-dark</v-icon>
          </template>
        </v-switch>
      </div>
    </v-app-bar>

    <v-main
      v-scroll="onScroll"
    >
      <v-container
        fluid
      >
        <template v-if="!this.isLocalhost()">
          <div class="mx-auto mb-4 text-center">
            <Adsense
              data-ad-client="ca-pub-8602791446931111"
              data-ad-slot="3626519086"
              data-ad-format="auto"
              data-full-width-responsive="yes"
            ></Adsense>
          </div>
        </template>
        <template v-if="loaded">
          <router-view />
        </template>
        <template v-else>
          <v-row
            class="fill-height"
            align-content="center"
            justify="center"
            :style="contentHeight"
          >
            <v-col cols="6">
              <v-progress-linear
                v-model="progress"
                color="light-blue"
                height="24"
                striped
              >
                <template v-slot:default="{ value }">
                  <strong>{{ Math.ceil(value) }}%</strong>
                </template>
              </v-progress-linear>
            </v-col>
          </v-row>
        </template>
        <template v-if="!this.isLocalhost()">
          <div class="mx-auto my-3 text-center">
            <Adsense
              data-ad-client="ca-pub-8602791446931111"
              data-ad-slot="3626519086"
              data-ad-format="auto"
              data-full-width-responsive="yes"
            ></Adsense>
          </div>
        </template>
      </v-container>
    </v-main>

    <v-footer
      ref="footer"
      padless
      class="transparent"
    >
      <Footer />
    </v-footer>

    <template>
      <v-container
        class="text-right"
        style="position: fixed; display: inline-block; bottom: 54px; right: 10px; width: 56px; z-index: 5;"
      >
        <v-fab-transition>
          <v-btn
            v-show="!hidden"
            fab
            dark
            absolute
            bottom
            right
            :active-class="$vuetify.theme.isDark ? 'amber darken-2' : 'light-blue darken-1'"
            :color="$vuetify.theme.isDark ? 'amber darken-3' : 'light-blue darken-2'"
            elevation="3"
            @click="$vuetify.goTo(0)"
          >
            <v-icon
              dark
            >mdi-arrow-up</v-icon>
          </v-btn>
        </v-fab-transition>
      </v-container>
    </template>

  </v-app>
</template>

<script>
import SideMenu from './components/SideMenu'
import Footer   from './components/Footer'

export default {
  name: 'App',

  components: {
    SideMenu,
    Footer,
  },

  data: () => ({
    progress: 0,
    drawer: false,
    darkTheme: true,
    labels: {
      title: 'MHRise: Skill Simulator',
    },
    hidden: true,
    loaded: false,
  }),

  watch: {
    darkTheme (value) {
      this.$vuetify.theme.isDark = value
    },
    progress (value) {
      if (value >= 100) {
        this.sleep(300).then(() => {
          this.loaded = true
        })
      }
    },
  },

  created() {
    this.darkTheme = this.$vuetify.theme.isDark
    this.getMasterData()
    this.loadUserData()
    if (process.env.NODE_ENV !== 'production') {
      console.log('App.vue::isLocalhost:', this.isLocalhost())
    }
  },

  mounted() {
    this.$root.$on('update:drawer', value => {
      this.drawer = value
    })
  },

  computed: {
    contentHeight () {
      let _dec = this.isLocalhost() ? (64 + 113): (64 + 113 + 280 * 2)
      return `height: ${this.$vuetify.breakpoint.height - _dec}px`
    },
  },

  methods: {
    onScroll: function() {
      this.hidden = window.scrollY <= 64
    },
  },
}
</script>
