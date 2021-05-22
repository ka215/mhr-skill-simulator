<template>
  <v-list
    subheader
  >
    <v-subheader
      class="sub-headline--text"
    >{{ labels.title }}</v-subheader>
    <Parameter name="defense" :baseValue="totals.defense" />
    <Parameter name="fire"    :baseValue="totals.fire_resistance" />
    <Parameter name="water"   :baseValue="totals.water_resistance" />
    <Parameter name="thunder" :baseValue="totals.thunder_resistance" />
    <Parameter name="ice"     :baseValue="totals.ice_resistance" />
    <Parameter name="dragon"  :baseValue="totals.dragon_resistance" />
  </v-list>
</template>

<script>
import Parameter from '@/components/Parameter'

export default {
  name: 'DefenseStatus',

  components: {
    Parameter,
  },

  data: () => ({
    labels: {
      title: '防御ステータス',
    },
    totals: {
      'defense': 1,
      'fire_resistance': 0,
      'water_resistance': 0,
      'thunder_resistance': 0,
      'ice_resistance': 0,
      'dragon_resistance': 0,
    }
  }),

  created() {
    if (this.$store.getters.equipmentExists()) {
      this.aggregateStatus()
    }
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
            this.aggregateStatus()
            break
          case 'setArmorLevel':
            this.aggregateStatus()
            break
        }
      }
    })
  },

  methods: {
    aggregateStatus: function() {
      // From armors:
      let armors = ['head', 'chest', 'arms', 'waist', 'legs'],
          status = {
            defense: 1,
            fire_resistance: 0,
            water_resistance: 0,
            thunder_resistance: 0,
            ice_resistance: 0,
            dragon_resistance: 0,
          }
      armors.forEach(part => {
        //console.log('aggregateStatus:', part, this.$store.getters.equipmentExists(part))
        if (this.$store.getters.equipmentExists(part)) {
          status.defense += this.$store.state[part].data.defense
          status.defense += (this.$store.state[part].level - 1) * 2
          status.fire_resistance += this.$store.state[part].data.fire_resistance
          status.water_resistance += this.$store.state[part].data.water_resistance
          status.thunder_resistance += this.$store.state[part].data.thunder_resistance
          status.ice_resistance += this.$store.state[part].data.ice_resistance
          status.dragon_resistance += this.$store.state[part].data.dragon_resistance
        }
      })
      // From weapon:
      /* Defense bonus is added by Parameter.vue, so we'll skip them here.
      if (this.$store.getters.equipmentExists('weapon')) {
        status.defense += this.$store.getters.equipmentKindOf('weapon', 'defense_bonus')
      }
      */
      // From skills:
      
      
      this.totals = status
      //console.log(status)
    },
  },

}
</script>