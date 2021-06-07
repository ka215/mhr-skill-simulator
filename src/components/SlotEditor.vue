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
              <div class="col-6 ma-0 px-1 py-0">
                <v-autocomplete
                  v-model="values[`slot${n}`]"
                  :items="setDecorations(item[`slot${n}`])"
                  flat
                  clearable
                  background-color="transparent"
                  color="primary"
                  item-text="name"
                  item-value="id"
                  @change="changeSlots()"
                >
                  <template v-slot:item="data">
                    <v-list-item-icon>
                      <v-icon
                        dense
                        :class="`rare-${data.item.rarity}--text`"
                      >mdi-numeric-{{ data.item.slot }}-circle-outline</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                      <v-list-item-title
                        :class="`rare-${data.item.rarity}--text`"
                      >{{ data.item.name }}</v-list-item-title>
                      <v-list-item-subtitle
                        class="text-caption"
                      >({{ Object.keys(data.item.skills).join() }})</v-list-item-subtitle>
                    </v-list-item-content>
                  </template>
                </v-autocomplete>
              </div>
              <div class="col-5 ma-0 px-4 py-0">
                <template v-if="values[`slot${n}`]">
                  {{ selectedSkillName(n) }}
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
//import mockData from '@/../public/mock_data.json'

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
    decorations: [],
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
    this.$root.$on('open:SlotEditor', (value) => {
      this.decorations = this.$store.state.decorations.concat()
      this.decorations.sort((a, b) => {
        return a.ruby_name.localeCompare(b.ruby_name, 'ja')
      })
      this.item = value
      this.labels.name = this.item.name
      this.loadSlotStatus()
      //console.log(`SlotEditor.vue::created:on.open:SlotEditor`, this.item, this.decorations.length)
      this.dialog = true
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
      return this.decorations.filter((item) => { return item.slot <= slot })
    },
    selectedSkillName: function(slot) {
      let skillId = this.values[`slot${slot}`],
          oneItem = this.decorations.find(item => item.id == skillId)
      return Object.keys(oneItem.skills).join()
    },
    loadSlotStatus: function() {
      let currentSlots = this.$store.getters.currentSlotsKindOf(this.getEquipmentKind(this.item))
      // console.log('SlotEditor.vue::loadSlotStatus', this.item, currentSlots)
      this.values = currentSlots
    },
    changeSlots: function() {
      //console.log('!:', this.values)
      this.$store.dispatch('setEquipment', {property: this.getEquipmentKind(this.item), slots: this.values})
    },
    commitEdit: function() {
      // console.log('SlotEditor.vue::commitEdit:save', this.values)
      this.changeSlots()
      this.dialog = false
    },
  },

}
</script>