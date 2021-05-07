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
        <v-card-title>{{ labels.title }}</v-card-title>
        <v-divider />
        <v-card-text
          v-if="item"
          class="py-12"
        >
          装備変更用ダイアログ
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
          <v-btn
            text
            @click="commitEdit"
          >{{ labels.commit }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
//import mockData from '@/../public/mock_data.json'

export default {
  name: 'EquipChanger',

  components: {
    //
  },

  props: {
    //
  },

  data: () => ({
    dialog: false,
    labels: {
      title: '装備変更',
      name: '',
      close: '閉じる',
      commit: '決定',
    },
    item: null,
  }),

  watch: {
    dialog: function (value) {
      if (!value) {
        //this.values = { slot1: null, slot2: null, slot3: null }
      }
    },
  },

  created() {
    //this.decorations = mockData.decorations
    this.$root.$on('open:EquipChanger', (value) => {
      this.item = value
      this.labels.name = this.item.name
      //this.loadSlotStatus()
      this.dialog = true
      console.log(`SlotEditor.vue::created:on.open:EquipChanger`, this.item)
    })
  },

  mounted() {
    //
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
    loadEquipItems: function() {
      console.log('EquipChanger.vue::loadEquipItems', this.item)
      // Loading...
    },
    commitEdit: function() {
      console.log('EquipChanger.vue::commitEdit:save', this.values)
      // Saving...
      this.dialog = false
    },
  },

}
</script>