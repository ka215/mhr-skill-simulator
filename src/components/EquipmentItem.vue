<template>
  <v-card
    outlined
    tile
    class="d-flex flex-wrap mb-2 pa-2"
  >
    <template v-if="loading">
      <div class="pa-2 text-center" style="width:100%">
        <v-progress-circular
          :size="32"
          :width="3"
          indeterminate
          :color="`grey ${$vuetify.theme.isDark ? 'darken-3': 'ligten-4'}`"
        ></v-progress-circular>
      </div>
    </template>
    <template v-else-if="item && item.name">
      <v-card-text
        class="text-subtitle-2 muted--text mx-0 py-1 px-0 col-2"
      >
        {{ equipmentType }}
      </v-card-text>
      <v-card-title
        :class="['text-subtitle-1', `rare-${item.rarity}--text`, 'mx-0', 'pa-1', 'col-8']"
      >{{ item.name }}</v-card-title>
      <v-card-title
        v-if="item.rarity > 0"
        :class="['text-caption', `rare-${item.rarity}--text`, 'text-no-wrap', 'mx-0', 'py-1', 'px-0', 'col-2']"
      >RARE {{ item.rarity }}</v-card-title>
      <v-card-actions
        class="d-flex flex-wrap justify-space-between py-1 px-0 col-12"
      >
        <div
          class="d-flex justify-start pa-0 col-6 col-md-3"
        >
          <Talisman :slotType="item.slot1" attached="" />
          <Talisman :slotType="item.slot2" attached="" />
          <Talisman :slotType="item.slot3" attached="" />
        </div>
        <template v-if="isArmor($props.type)">
          <div
            data-ref="levelSelector"
            class="d-flex justify-center align-center px-1 py-0 col-6 col-md-4"
          >
            <label class="px-2">Lv</label>
            <v-select
              v-model="selectedLevel"
              :items="levelItems"
              _label="` 1/${item.max_level}`"
              :hint="`最大: ${item.max_level}`"
              height="24"
              dense
              persistent-hint
              single-line
            ></v-select>
          </div>
        </template>
        <div class="text-right pa-0 col-12 col-md-5">
          <v-btn
            class="btn-tertiary mr-2"
            elevation="0"
            small
            :disabled="!hasSlot"
            @click="openDialog('SlotEditor')"
          >{{ labels.edit }}</v-btn>
          <v-btn
            class="btn-secondary"
            elevation="0"
            small
            @click="openDialog('EquipChanger')"
          >{{ labels.change }}</v-btn>
        </div>
      </v-card-actions>
    </template>
    <template v-else>
      <v-card-text
        class="text-subtitle-2 muted--text mx-0 py-1 px-0 col-2"
      >{{ equipmentType }}</v-card-text>
      <v-card-title
        class="text-subtitle-1 muted--text mx-0 pa-1 col-8"
      ><v-icon class="muted--text">mdi-minus</v-icon></v-card-title>
      <v-card-title
        class="text-caption muted--text text-no-wrap mx-0 py-1 px-0 col-2"
      ><v-icon class="muted--text">mdi-minus</v-icon></v-card-title>
      <v-card-actions
        class="d-flex flex-wrap justify-space-between py-1 px-0 col-12"
      >
        <div
          class="d-flex align-center"
        >
          <Talisman slotType="0" attached="" />
          <Talisman slotType="0" attached="" />
          <Talisman slotType="0" attached="" />
        </div>
        <div class="text-right">
          <v-btn
            class="btn-tertiary mr-2"
            elevation="0"
            small
            disabled
          >{{ labels.edit }}</v-btn>
          <v-btn
            class="btn-secondary"
            elevation="0"
            small
            @click="openDialog('EquipChanger')"
          >{{ labels.change }}</v-btn>
        </div>
      </v-card-actions>
    </template>
  </v-card>
</template>

<style lang="scss">
[data-ref=levelSelector] {
  .v-select__selection--comma { position: absolute; left: 50%; transform: translateX(-66%); }
  .v-messages__message { text-align: center; }
}
</style>

<script>
import Talisman from '@/components/Talisman'

export default {
  name: 'EquipmentItem',

  components: {
    Talisman,
  },

  props: {
    type: {
      Type: String,
      default: null,
    },
    id: {
      Type: Number,
      default: 0,
    }
  },

  data: () => ({
    item: null,
    oldItem: null,
    selectedLevel: null,
    labels: {
      weapon: '武器',
      head: '頭部',
      chest: '胸部',
      arms: '腕部',
      waist: '腰部',
      legs: '脚部',
      talisman: '護石',
      edit: '着脱',
      change: '変更',
    },
    loading: true,
  }),

  watch: {
    selectedLevel: function (newlv/*, oldlv*/) {
      let part = ['head', 'chest', 'arms', 'waist', 'legs'][this.item.part]
      //console.log("EquipmentItem.vue::changed %s selectedLevel: %s -> %s", part, oldlv, newlv)
      //console.log('Current stored part level: %d', this.$store.getters.armorLevelKindOf(part))
      this.$store.dispatch('setArmorLevel', {part: part, level: newlv})
      //console.log('Saved to stored part level: %d', this.$store.getters.armorLevelKindOf(part))
    },
  },

  created() {
    this.initialize()
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action, state) => {
        if ('setEquipment' === action.type && this.$props.type === action.payload.property) {
          this.oldItem = this.item
          this.item = state[action.payload.property].data
          if (this.isArmor(this.$props.type) && Object.keys(this.item).length > 0) {
            this.selectedLevel = state[action.payload.property].level || 1
          }
          //console.log('EquipmentItem.vue::After changing %s: ', this.$props.type, this.oldItem, this.item)
        }
      }
    })
  },

  computed: {
    equipmentType() {
      switch (this.$props.type) {
        case 'weapon':
          return this.item && Object.prototype.hasOwnProperty.call(this.item, 'name') && this.item.name !== ''
            ? this.getEquipType('weapon', this.item.type)
            : this.labels.weapon
        case 'talisman':
          return this.item && Object.prototype.hasOwnProperty.call(this.item, 'name')
            ? this.getEquipType('talisman', 0)
            : this.labels.talisman
        default:
          return this.item && Object.prototype.hasOwnProperty.call(this.item, 'part')
            ? this.getEquipType('armor', this.item.part)
            : this.labels[this.$props.type]
      }
    },
    hasSlot() {
      return this.item && (this.item.slot1 + this.item.slot2 + this.item.slot3) > 0
    },
    levelItems() {
      return this.item ? Array.from(Array(this.item.max_level), (v, k) => k + 1): []
    },
  },

  methods: {
    openDialog: function (target) {
      //console.log(`EquipmentItem.vue::beforeEmit.open:${target}`, this.$props.type, this.item, this.$store.getters.weaponsKindOf(this.$props.type))
      if (!this.item || !this.item.id) {
        switch (this.$props.type) {
          case 'weapon':
            this.item = {id: 0, name: '', type: 0, rarity: 0, attack: 0, affinity: 0, element1: 0, element2: 0, slot1: 0, slot2: 0, slot3: 0}
            break
          case 'talisman':
            this.item = {id: 0, name: '', rarity: 0, slot1: 0, slot2: 0, slot3: 0}
            break
          default:
            //this.item = {id: 0, name: '', part: ['head', 'chest', 'arms', 'waist', 'legs'].indexOf(this.$props.type), rarity: 0, defense: 0, slot1: 0, slot2: 0, slot3: 0}
            this.item = {id: 0, name: '', part: this.getArmorPartIndex(this.$props.type), rarity: 0, defense: 0, slot1: 0, slot2: 0, slot3: 0}
            break
        }
      }
      this.$root.$emit(`open:${target}`, this.item)
    },
    initialize: function () {
      if (this.$props.id) {
        this.item = this.$store.getters.equipmentKindOf(this.$props.type)
        if (this.isArmor(this.$props.type)) {
          this.selectedLevel = this.$store.getters.armorLevelKindOf(this.item.part)
        }
      }
      this.loading = false
    },
 },

}
</script>