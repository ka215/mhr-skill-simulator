<template>
  <v-app>
    <v-navigation-drawer
      v-model="drawer"
      absolute
      _app
      _bottom
      temporary
    >
      <SideMenu />
    </v-navigation-drawer>

    <v-app-bar
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
        <!-- router-view / -->
        <MainView />
      </v-container>
    </v-main>

    <v-footer
      app
      padless
      class="transparent"
    >
      <Footer />
    </v-footer>
  </v-app>
</template>

<script>
import SideMenu from './components/SideMenu'
import MainView from './components/MainView'
import Footer   from './components/Footer'

export default {
  name: 'App',

  components: {
    SideMenu,
    MainView,
    Footer,
  },

  data: () => ({
    drawer: false,
    darkTheme: true,
    labels: {
      title: 'MHRise: Skill Simulator',
    },
  }),

  watch: {
    darkTheme (value) {
      this.$vuetify.theme.isDark = value
    }
  },

  created() {
    this.darkTheme = this.$vuetify.theme.isDark
  },

  mounted() {
    this.$root.$on('update:drawer', value => {
      this.drawer = value
    })
  },
};
</script>
