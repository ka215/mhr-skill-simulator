<template>
  <v-card
    outlined
    tile
    class="pa-1"
  >
    <h3
      class="headline--text text-center mb-2"
    >{{ labels.title }}</h3>
    <EquipmentItem type="weapon"   :id="currentIds.weapon" />
    <EquipmentItem type="head"     :id="currentIds.head" />
    <EquipmentItem type="chest"    :id="currentIds.chest" />
    <EquipmentItem type="arms"     :id="currentIds.arms" />
    <EquipmentItem type="waist"    :id="currentIds.waist" />
    <EquipmentItem type="legs"     :id="currentIds.legs" />
    <EquipmentItem type="talisman" :id="currentIds.talisman" />
    <v-divider />
    <v-card-actions>
      <v-btn
        class="btn-default"
        elevation="0"
        to="talismans"
      >{{ labels.edit }}</v-btn>
      <v-spacer />
      <v-btn
        :class="['grey', {'darken-2': $vuetify.theme.isDark}, {'lighten-1': !$vuetify.theme.isDark}]"
        elevation="0"
        :disabled="!isEquipExists"
        @click="removeEquipments"
      >{{ labels.reset }}</v-btn>
      <v-btn
        class="btn-primary"
        elevation="0"
        :disabled="!isEquipExists"
        @click="saveMyset"
      >{{ labels.save }}</v-btn>
    </v-card-actions>
    <template>
      <SlotEditor />
      <EquipChanger />
    </template>
  </v-card>
</template>

<script>
import EquipmentItem from '@/components/EquipmentItem'
import SlotEditor from '@/components/SlotEditor'
import EquipChanger from '@/components/EquipChanger'

export default {
  name: 'Equipment',

  components: {
    EquipmentItem,
    SlotEditor,
    EquipChanger,
  },

  data: () => ({
    labels: {
      title: '装備・装飾品',
      edit: '護石登録',
      reset: '全部外す',
      save: 'マイセット登録',
    },
    currentIds: {
      weapon:   0,
      head:     0,
      chest:    0,
      arms:     0,
      waist:    0,
      legs:     0,
      talisman: 0,
    },
    //oldId: null,
  }),

  created() {
    for (const key in this.currentIds) {
      //console.log(`Equipment.vue::created:`, key, this.$store.getters.equipmentKindOf(key, 'id'))
      this.currentIds[key] = this.$store.getters.equipmentKindOf(key, 'id') || 0
    }
  },

  mounted() {
    this.$store.subscribeAction({
      /*
      before: (action) => {
        if ('setEquipment' === action.type) {
          this.oldId = this.currentIds[action.payload.property]
              newId = action.payload.data.id
          if (oldId != newId) {
            this.currentIds[action.payload.property] = newId
            console.log('Equipment.vue::Changed equipment:', action.payload.property, oldId, ' -> ', newId)
          }
        }
      },
      */
      after: (action/*, state*/) => {
        if ('setEquipment' === action.type) {
          if (Object.prototype.hasOwnProperty.call(action.payload, 'data')) {
            let oldId = this.currentIds[action.payload.property],
                newId = action.payload.data.id
            if (oldId != newId) {
              this.currentIds[action.payload.property] = newId
              //console.log('Equipment.vue::After changing %s: %s -> %s', action.payload.property, oldId, newId)
            }
          }
        }
      }
    })
  },

  computed: {
    isEquipExists: function() {
      return this.$store.getters.equipmentExists()
    },
  },

  methods: {
    removeEquipments: function() {
      ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'].forEach(kind => {
        let payload = {property: kind, data: {}, slots: {}}
        if (this.isArmor(kind)) {
          payload.level = null
        }
        this.$store.dispatch('setEquipment', payload)
      })
    },
    saveMyset: function() {
      alert('Not available yet.')
    },
  },

}
</script>