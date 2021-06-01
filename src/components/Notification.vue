<template>
  <v-row
    justify="center"
    class="ma-0 pa-0"
  >
    <v-dialog
      v-model="dialog"
      :max-width="dialogWidth"
      overlay-color="grey darken-3"
      overlay-opacity="0.75"
    >
      <v-card>
        <template v-if="labels.title">
          <v-card-title>{{ labels.title }}</v-card-title>
          <v-divider />
        </template>
        <v-card-text
          class="text-center"
          v-html="content"
        ></v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'Notification',

  data: () => ({
    dialog: false,
    labels: {
      title: '',
      close: '閉じる',
    },
    content: '',
  }),

  created() {
    this.$root.$on('open:notification', (notices) => {
      if (Object.prototype.hasOwnProperty.call(notices, 'title') && notices.title) {
        this.labels.title = notices.title
      }
      if (Object.prototype.hasOwnProperty.call(notices, 'messages') && notices.messages.length > 0) {
        this.content = notices.messages.join('<br>')
      }
      //console.log('Notification.vue::open:notification', notices, this.title, this.content)
      this.dialog = true
    })
  },

  computed: {
    dialogWidth () {
      // console.log('dialogWidth', this.$vuetify.breakpoint.name)
      switch (this.$vuetify.breakpoint.name) {
        case 'xs': return `${this.$vuetify.breakpoint - 30}px`
        case 'sm':
        default: return '600px'
      }
    },
  },

}
</script>