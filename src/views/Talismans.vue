<template>
  <v-container
    fluid
  >
    <TalismanRegister />
    <div class="py-3"></div>
    <TalismanManager />
    <template>
      <Notification />
      <Importer />
    </template>
  </v-container>
</template>

<script>
import TalismanRegister from '@/components/TalismanRegister'
import TalismanManager from '@/components/TalismanManager'
import Notification from '@/components/Notification'
import Importer from '@/components/Importer'

export default {
  name: 'Talismans',

  components: {
    TalismanRegister,
    TalismanManager,
    Notification,
    Importer,
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
