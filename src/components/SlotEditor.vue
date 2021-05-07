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
        <v-card-title>{{ labels.title }} : {{ labels.name }}</v-card-title>
        <v-divider />
        <v-card-text
          v-if="item"
          class="py-1"
        >
          <template
            v-for="n in 3"
          >
            <v-list-item
              :key="n"
              v-if="item[`slot${n}`] > 0"
              class="d-flex justify-start"
            >
              <div class="col-1 ma-0 px-1 py-0">
                <Talisman :slotType="item[`slot${n}`]" :attached="values[`slot${n}`]" />
              </div>
              <div class="col-5 ma-0 px-1 py-0">
                <v-autocomplete
                  v-model="values['slot' + n]"
                  :items="setDecorations(item[`slot${n}`])"
                  flat
                  clearable
                  background-color="transparent"
                  color="primary"
                ></v-autocomplete>
              </div>
              <div class="col-6 ma-0 px-4 py-0">
                <template v-if="values[`slot${n}`]">
                  {{ labels.skillName }}
                </template>
                <template v-else>
                  <v-icon class="muted--text">mdi-minus</v-icon>
                </template>
              </div>
            </v-list-item>
          </template>
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
import Talisman from '@/components/Talisman'
import mockData from '@/../public/mock_data.json'

export default {
  name: 'SlotEditor',

  components: {
    Talisman,
  },

  props: {
    //
  },

  data: () => ({
    dialog: false,
    labels: {
      title: '装飾品の着脱',
      name: '',
      edit: '着脱',
      close: '閉じる',
      commit: '決定',
      skillName: 'スキル名を表示',
    },
    item: null,
    decorations: {},
    values: {
      slot1: null,
      slot2: null,
      slot3: null,
    },
  }),

  watch: {
    dialog: function (value) {
      if (!value) {
        this.values = { slot1: null, slot2: null, slot3: null }
      }
    },
  },

  created() {
    this.decorations = mockData.decorations
    this.$root.$on('open:SlotEditor', (value) => {
      this.item = value
      this.labels.name = this.item.name
      this.loadSlotStatus()
      this.dialog = true
      console.log(`SlotEditor.vue::created:on.open:SlotEditor`, this.item)
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
    setDecorations: function(slot) {
      console.log(slot)
      switch (slot) {
        case 1: return this.decorations.slot1
        case 2: return this.decorations.slot2.concat(this.decorations.slot1)
        case 3: return this.decorations.slot3.concat(this.decorations.slot2, this.decorations.slot1)
      }
    },
    loadSlotStatus: function() {
      console.log('SlotEditor.vue::loadSlotStatus', this.item)
      // Loading...
    },
    commitEdit: function() {
      console.log('SlotEditor.vue::commitEdit:save', this.values)
      // Saving...
      this.dialog = false
    },
  },

}
</script>