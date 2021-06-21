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
        <v-card-title
          :class="[{'amber--text': $vuetify.theme.isDark}, {'blue-grey--text': !$vuetify.theme.isDark}, {'text--accent-4': $vuetify.theme.isDark}, {'text--darken-4': !$vuetify.theme.isDark}]"
        >{{ labels.title }}</v-card-title>
        <v-divider />
        <v-card-text>
          <div
            class="pt-6 pb-2"
            v-html="labels.content"
          ></div>
          <v-file-input
            v-model="inputFile"
            accept=".json, .jsonp, text/plain, application/json"
            show-size
            :label="labels.input"
            outlined
            dense
            class="mt-2"
          ></v-file-input>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-1': !$vuetify.theme.isDark}]"
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
          <v-btn
            text
            :class="[{'amber--text': $vuetify.theme.isDark}, {'light-blue--text': !$vuetify.theme.isDark}, 'text--accent-4']"
            :disabled="!inputFile"
            @click="doSubmit()"
          >{{ labels.commit }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'Importer',

  data: () => ({
    dialog: false,
    labels: {
      title: 'ファイルのインポート',
      content: [],
      input: 'アップロードファイル',
      close: '閉じる',
      commit: 'インポートする',
    },
    dataType: null,
    inputFile: null,
  }),

  created() {
    this.$root.$on('open:importer', (options) => {
      for (let [key, value] of Object.entries(options)) {
        switch (key) {
          case 'type':
            this.dataType = value
            break
          case 'content':
            this.labels.content = value.join('<br>')
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
      switch (this.$vuetify.breakpoint.name) {
        case 'xs': return `${this.$vuetify.breakpoint - 30}px`
        case 'sm':
        default: return '600px'
      }
    },
  },

  methods: {
    doSubmit: function() {
      const reader = new FileReader()
      let data = null
      reader.readAsText(this.inputFile)
      reader.onload = () => {
        data = JSON.parse(reader.result)
        this.inputFile = null
        this.dialog = false
        this.import(this.dataType, data)
      }
    },
  },

}
</script>