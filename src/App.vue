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
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-toolbar-title>{{ labels.title }}</v-toolbar-title>
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

    <v-main>
      <v-container
        fluid
      >
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
      </v-container>
    </v-main>

    <v-footer
      ref="footer"
      padless
      class="transparent"
    >
      <Footer />
    </v-footer>
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
    loaded: true,
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
    console.log('App.vue::isLocalhost:', this.isLocalhost())
  },

  mounted() {
    this.$root.$on('update:drawer', value => {
      this.drawer = value
    })
  },

  computed: {
    contentHeight () {
      return `height: ${this.$vuetify.breakpoint.height - 64 - 113}px`
    },
  },
}
</script>
