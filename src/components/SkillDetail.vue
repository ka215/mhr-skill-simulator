<template>
  <v-row
    justify="center"
    class="ma-0 pa-0"
  >
    <v-dialog
      v-model="dialog"
      :max-width="dialogWidth"
      overlay-color="grey darken-3"
      overlay-opacity="0.75"
    >
      <v-card>
        <v-card-title>{{ skill || labels.title }}</v-card-title>
        <v-divider />
        <v-card-subtitle
          class="py-3"
        >Level {{ level }}</v-card-subtitle>
        <v-card-text
          class="py-6"
        >
          スキルの説明用ダイアログ
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
//import mockData from '@/../public/mock_data.json'

export default {
  name: 'SkillDetail',

  components: {
    //
  },

  props: {
    //
  },

  data: () => ({
    dialog: false,
    labels: {
      title: 'スキル詳細',
      close: '閉じる',
    },
    skill: null,
    level: null,
  }),

  watch: {
    dialog: function (value) {
      if (!value) {
        //this.values = { slot1: null, slot2: null, slot3: null }
      }
    },
  },

  created() {
    this.$root.$on('open:SkillDetail', (...args) => {
      const [skill, level] = args
      this.skill = skill
      this.level = level
      this.dialog = true
      console.log(`SkillDetail.vue::created:on.open:SkillDetail`, this.skill, this.level)
    })
  },

  mounted() {
    //
  },

  computed: {
    dialogWidth () {
      // console.log('dialogWidth', this.$vuetify.breakpoint.name)
      switch (this.$vuetify.breakpoint.name) {
        case 'xs': return `${this.$vuetify.breakpoint - 30}px`
        case 'sm':
        default: return '600px'
      }
    },
  },

  methods: {
    loadEquipItems: function() {
      console.log('EquipChanger.vue::loadEquipItems', this.item)
      // Loading...
    },
    commitEdit: function() {
      console.log('EquipChanger.vue::commitEdit:save', this.values)
      // Saving...
      this.dialog = false
    },
  },

}
</script>