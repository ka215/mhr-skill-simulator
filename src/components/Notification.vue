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
          <v-card-title
            :class="[{'amber--text': $vuetify.theme.isDark}, {'blue-grey--text': !$vuetify.theme.isDark}, {'text--accent-4': $vuetify.theme.isDark}, {'text--darken-4': !$vuetify.theme.isDark}]"
          >{{ labels.title }}</v-card-title>
          <v-divider />
        </template>
        <v-card-text>
          <div
            class="pt-6 pb-2 text-center"
            v-html="content"
          ></div>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-1': !$vuetify.theme.isDark}]"
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
          <template v-if="emitEvent">
            <v-btn
              text
              :class="[{'amber--text': $vuetify.theme.isDark}, {'light-blue--text': !$vuetify.theme.isDark}, 'text--accent-4']"
              @click="doSubmit()"
            >{{ labels.commit }}</v-btn>
          </template>
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
      commit: '送信する',
    },
    content: '',
    emitEvent: null,
    emitArgs: null,
  }),

  created() {
    this.$root.$on('open:notification', (notices) => {
      for (let [key, value] of Object.entries(notices)) {
        switch (key) {
          case 'messages':
            this.content = value.join('<br>')
            break
          case 'emit':
            this.emitEvent = value
            break
          case 'data':
            this.emitArgs = value
            break
          default:
            this.labels[key] = value
            break
        }
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

  methods: {
    doSubmit: function() {
      this.$root.$emit(this.emitEvent, this.emitArgs)
      this.dialog = false
    },
  },

}
</script>