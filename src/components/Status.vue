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
    <PlayerConfig />
  </v-card>
</template>

<script>
import AttackStatus from '@/components/AttackStatus'
import DefenseStatus from '@/components/DefenseStatus'
import PlayerConfig from '@/components/PlayerConfig'

export default {
  name: 'Status',

  components: {
    AttackStatus,
    DefenseStatus,
    PlayerConfig,
  },

  data: () => ({
    labels: {
      title: 'ステータス',
    },
  }),
}
</script>