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
import mockData from '@/../public/mock_data.json'

export default {
  name: 'DefenseStatus',

  components: {
    Parameter,
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: '防御ステータス',
    },
    totals: {
      'defense': 0,
      'fire_resistance': 0,
      'water_resistance': 0,
      'thunder_resistance': 0,
      'ice_resistance': 0,
      'dragon_resistance': 0,
    }
  }),

  created() {
    for (let key in this.totals) {
      this.totals[key] = mockData.armors.reduce((acc, cur) => acc + parseInt(cur[key], 10), 0)
    }
  },

  mounted() {
    //
  },

  computed: {
    //
  },

  methods: {
    //
  },

}
</script>