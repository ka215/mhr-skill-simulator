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
        @click="saveLoadouts"
      >
        <template v-if="$store.state.now_loadouts">{{ labels.update }}</template>
        <template v-else>{{ labels.register }}</template>
      </v-btn>
    </v-card-actions>
    <template>
      <SlotEditor />
      <EquipChanger />
      <LoadoutsEditor />
      <Notification />
    </template>
  </v-card>
</template>

<script>
import EquipmentItem from '@/components/EquipmentItem'
import SlotEditor from '@/components/SlotEditor'
import EquipChanger from '@/components/EquipChanger'
import LoadoutsEditor from '@/components/LoadoutsEditor'
import Notification from '@/components/Notification'

export default {
  name: 'Equipment',

  components: {
    EquipmentItem,
    SlotEditor,
    EquipChanger,
    LoadoutsEditor,
    Notification,
  },

  data: () => ({
    labels: {
      title: '装備・装飾品',
      edit: '護石登録',
      reset: '全部外す',
      register: 'マイセット登録',
      update: 'マイセット更新',
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
      after: (action/*, state*/) => {
        if ('setEquipment' === action.type) {
          if (Object.prototype.hasOwnProperty.call(action.payload, 'data')) {
            let oldId = this.currentIds[action.payload.property],
                newId = action.payload.data ? action.payload.data.id: 0
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
        this.$store.dispatch('initData', {property: 'now_loadouts', data: null})
        this.$store.dispatch('setEquipment', payload)
      })
    },
    saveLoadouts: function() {
      let _act = this.$store.state.now_loadouts ? 'update': 'register'
      this.$root.$emit('save:loadouts', {action: _act})
    },
  },

}
</script>