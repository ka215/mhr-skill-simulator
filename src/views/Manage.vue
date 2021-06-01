<template>
  <v-container
    fluid
    class="text--secondary mx-12 px-12"
  >
    <template v-if="loaded">
      <h3>Management Tools</h3>
      <div class="d-flex align-center my-2">
        <v-btn
          text
          @click="initStore()"
        ><v-icon>mdi-chevron-right</v-icon>Initialize Store</v-btn>
        <span class="text-caption">─ Initializes the currently cached store data.</span>
      </div>
      <div class="d-flex align-center my-2">
        <v-btn
          text
          @click="loadMasterData()"
        ><v-icon>mdi-chevron-right</v-icon>Load Master Data</v-btn>
        <span class="text-caption">─ Load master data from database. The loaded data will be cached in the store.</span>
      </div>
    </template>
    <template v-else>
      <v-row
        class="fill-height"
        align-content="center"
        justify="center"
        :style="contentHeight"
      >
        <v-col cols="6">
          <v-progress-linear
            v-model="progress"
            color="light-blue"
            height="24"
            striped
          >
            <template v-slot:default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </v-progress-linear>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script>
export default {
  name: 'Manage',

  components: {
    //
  },

  data: () => ({
    progress: 0,
    loaded: true,
  }),

  watch: {
    progress (value) {
      if (value >= 100) {
        this.sleep(300).then(() => {
          this.loaded = true
        })
      }
    },
  },

  created() {
    //
  },

  computed: {
    contentHeight () {
      return `height: ${this.$vuetify.breakpoint.height - 64 - 113}px`
    },
  },

  methods: {
    initStore: function() {
      this.$store.dispatch('resetState')
      //console.log(this.$store.state)
    },
    loadMasterData: function() {
      this.loaded = false
      this.$store.dispatch('resetState')
      this.getMasterData()
      //console.log(this.$store.state)
      //window.location.reload(true)
    },
  },
}
</script>
