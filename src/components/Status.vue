<template>
  <v-card
    outlined
    tile
    class="pa-1"
  >
    <h3
      class="headline--text text-center"
    >{{ labels.title }}</h3>
    <AttackStatus />
    <v-divider />
    <DefenseStatus />
    <v-divider />
    <ItemConfig />
  </v-card>
</template>

<script>
import AttackStatus from '@/components/AttackStatus'
import DefenseStatus from '@/components/DefenseStatus'
import ItemConfig from '@/components/ItemConfig'

export default {
  name: 'Status',

  components: {
    AttackStatus,
    DefenseStatus,
    ItemConfig,
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: 'ステータス',
    },
  }),

  created() {
    //
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