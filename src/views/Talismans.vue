<template>
  <v-container
    fluid
  >
    <TalismanRegister />
    <div class="py-3"></div>
    <TalismanManager />
    <template>
      <Notification />
    </template>
  </v-container>
</template>

<script>
import TalismanRegister from '@/components/TalismanRegister'
import TalismanManager from '@/components/TalismanManager'
import Notification from '@/components/Notification'

export default {
  name: 'Talismans',

  components: {
    TalismanRegister,
    TalismanManager,
    Notification,
  },

  data: () => ({
    //
  }),

  created() {
    //
  },

  methods: {
    //
  },
}
</script>
