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
        <v-card-title>{{ labels.title }}</v-card-title>
        <v-divider />
        <v-col
          cols="12"
          class="pa-0"
        >
          <v-simple-table
            class="skill-info"
          >
            <tr>
              <td
                class="skill-desc px-4"
                style="width: 50%;"
              >{{ labels.description }}</td>
              <td
                class="pa-0"
                style="width: 50%;"
              >
                <v-simple-table
                  v-if="skillData"
                  class="transparent skill-levels"
                >
                  <tbody>
                    <tr
                      v-for="lv in Number(skillData.max_lv)"
                      :key="lv"
                    >
                      <th
                        :class="`text-caption ${lv == currentLevel ? nowLevelClasses(): ''}`"
                        style="width: 60px;"
                      >Lv {{ lv }}</th>
                      <td
                        :class="`text-caption ${lv == currentLevel ? nowLevelClasses(): ''} text-left py-1`"
                        style="width:calc(100% - 60px);"
                      >{{ skillData.status[lv] }}</td>
                    </tr>
                  </tbody>
                </v-simple-table>
              </td>
            </tr>
          </v-simple-table>
        </v-col>
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
export default {
  name: 'SkillDetail',

  data: () => ({
    dialog: false,
    labels: {
      title: 'スキル名',
      description: 'スキル詳細',
      close: '閉じる',
    },
    currentLevel: null,
    skillData: null,
  }),

  /*
  watch: {
    dialog: function (value) {
      if (!value) {
        //this.values = { slot1: null, slot2: null, slot3: null }
      }
    },
  },
  */

  created() {
    this.$root.$on('open:SkillDetail', (...args) => {
      const [skill, level] = args
      this.currentLevel = level
      this.skillData = this.$store.state.skills.find(elm => elm.name === skill)
      this.labels.title = this.skillData.name
      this.labels.description = this.skillData.description
      //console.log(`SkillDetail.vue::created:on.open:SkillDetail`, this.skillData, this.currentLevel)
      this.dialog = true
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
    nowLevelClasses: function() {
      if (this.$vuetify.theme.isDark) {
        return 'font-weight-black amber--text text--accent-4 blue-grey darken-4'
      } else {
        return 'font-weight-black teal--text teal--accent-4 blue-grey lighten-4'
      }
    },
  },

}
</script>