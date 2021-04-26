<template>
  <v-card
    flat
    tile
    width="100%"
    class="transparent text-center"
  >
    <v-card-text>
      <v-btn
        v-for="item in icons"
        :key="item.icon"
        class="mx-4 font-weight-light"
        :href="item.href"
        text
        small
      >
        <v-icon
          size="20px"
          class="mr-1 grey--text"
        >mdi-{{ item.icon }}</v-icon>
        <span
          :class="`grey--text ${$vuetify.theme.isDark ? 'text--lighten-2': 'text--darken-2'}`"
        >{{ item.label }}</span>
      </v-btn>
    </v-card-text>
    <v-divider />
    <v-card-text
      class="grey--text text--darken-1"
    >
      {{ labels.version }} &mdash; {{ new Date().getFullYear() }} &copy; <strong>{{ labels.copyright }}</strong> {{ labels.poweredby }}
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'Footer',

  data: () => ({
    labels: {
      version:   'Ver.0.1.1',
      copyright: 'Monaural Sound ka2.org,',
      poweredby: 'Powered by MAGIC METHODS',
    },
    icons: [
      { label: 'Home',    icon: 'home',                 href: 'https://ka2.org', },
      { label: 'Contact', icon: 'email',                href: 'https://ka2.org/contact', },
      { label: 'Issues',  icon: 'alert-circle-outline', href: 'https://github.com/ka215/mhr-skill-simulator/issues', },
    ],
  }),

}
</script>
