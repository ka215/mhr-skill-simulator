<template>
  <v-card
    outlined
    tile
    class="pa-1"
  >
    <h3
      class="headline--text text-center"
    >{{ labels.title }}</h3>
    <v-simple-table>
      <template v-slot:default>
        <thead>
          <tr>
            <th colspan="2">{{ labels.th[0] }}</th>
            <th
              v-for="n in 7"
              :key="n"
            >{{ labels.th[n] }}</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="summary">
            <template v-if="!isEmptySummary()">
              <tr
                v-for="(lv, skill) in summary"
                :key="skill"
                @click="openDialog(skill, lv)"
              >
                <td>{{ skill }}</td>
                <td
                  :class="levelTextClass(skill, lv)"
                >Lv{{ displayLevel(skill, lv) }}</td>
                <td
                  :class="`bd-0 ${classInSlot(skill, 0)}`"
                >
                  {{ getSkillLv(skill, 0) }}
                </td>
                <td
                  v-for="n in 6"
                  :key="n"
                  :class="`bd-${n} ${classInSlot(skill, n)}`"
                >
                  {{ getSkillLv(skill, n) }}
                </td>
              </tr>
            </template>
            <template v-else>
              <tr class="no-hover-effect">
                <td colspan="9" class="text--secondary text-center py-12">
                  {{ labels.noSkill }}
                </td>
              </tr>
            </template>
          </template>
          <template v-else>
            <tr class="no-hover-effect">
              <td colspan="9" class="text--secondary text-center py-12">
                {{ labels.noData }}
              </td>
            </tr>
          </template>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="2">{{ labels.th[0] }}</th>
            <th
              v-for="n in 7"
              :key="n"
            >{{ labels.th[n] }}</th>
          </tr>
        </tfoot>
      </template>
    </v-simple-table>
    <v-row v-if="Object.keys(summary).length > 0">
      <v-col cols="12" class="d-flex justify-end mt-2">
        <v-icon small class="grey--text mr-1">mdi-alert-box-outline</v-icon>
        <span class="text-caption text--secondary mr-1" v-html="labels.hint"></span>
      </v-col>
    </v-row>
    <template>
      <SkillDetail />
    </template>
  </v-card>
</template>

<script>
import SkillDetail from '@/components/SkillDetail'

export default {
  name: 'SkillBreakdown',

  components: {
    SkillDetail,
  },

  data: () => ({
    labels: {
      title: 'スキルレベル内訳',
      th: [ '発動スキル', '武器', '頭部', '胸部', '腕部', '腰部', '脚部', '護石' ],
      noData: 'データがありません。装備を選択してください。',
      noSkill: '発動しているスキルはありません。',
      hint: 'スキルレベルが<span class="green--text">緑色</span>で表示されているものは、装飾品によって付与・強化されているスキルです',
    },
    skills: null,
    summary: null,
  }),

  watch: {
    summary: function(value) {
      //console.log('SkillBreakdown.vue::watch.summary:', value)
      this.$store.dispatch('setAggSkills', {aggrigation: value})
    }
  },

  created() {
    if (this.$store.getters.equipmentExists) {
      this.createBreakdown()
    }
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
            this.createBreakdown()
            break
        }
      }
    })
  },

  methods: {
    isEmptySummary: function() {
      //return !Object.keys(this.summary).length
      for (let i in this.summary) {
        return false
      }
      return true
    },
    levelTextClass: function (skill, lv) {
      let max_lv = this.getMaxSkillLv(skill)
      switch (true) {
        case lv > max_lv:   return 'excess-level--text'
        case lv == max_lv:  return 'max-level--text'
        case lv < max_lv:   return 'below-level--text'
      }
    },
    displayLevel: function (skill, lv) {
      let max_lv = this.getMaxSkillLv(skill)
      if (lv > max_lv) {
        lv = max_lv
      }
      return lv
    },
    classInSlot: function (skill, part) {
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']
      if (skill in this.skills[kinds[part]]) {
        //console.log('classInSlot:', skill, this.$store.getters.currentSlotsKindOf(kinds[part]), this.$store.getters.currentSkillsInSlots(kinds[part]))
        if (this.$store.getters.currentSkillsInSlots(kinds[part]).includes(skill)) {
          return this.$vuetify.theme.isDark ? 'green--text text--accent-4': 'green--text text--darken-2'
        } else {
          return ''
        }
      }
    },
    getSkillLv: function (skill, part) {
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']
      if (skill in this.skills[kinds[part]]) {
        return this.skills[kinds[part]][skill]
      }
    },
    getMaxSkillLv: function (skill) {
      let targetSkill = this.$store.state.skills.find(row => row.name === skill)
      return targetSkill.max_lv
    },
    openDialog: function (skill, lv) {
      this.$root.$emit('open:SkillDetail', skill, lv)
    },
    createBreakdown: function() {
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'],
          currentSkills = {},
          tempSummary = {},
          pairs
      //console.log('SkillBreakdown.vue::setEquipment.after: Changed "%s"', kind)
      kinds.forEach(v => {
        // Get builtin skills from equipment item
        currentSkills[v] = Object.prototype.hasOwnProperty.call(this.$store.state[v].data, 'skills') ? Object.assign({}, this.$store.state[v].data.skills): {}
        // Get skills from decorations attatched slots
        if (Object.keys(this.$store.state[v].slots).length > 0) {
          for (let [, _id] of Object.entries(this.$store.state[v].slots)) {
            if (_id == null) {
              continue
            }
            let decorationData = this.$store.getters.itemsById('decorations', _id),
                skillName = Object.keys(decorationData.skills).join()
            if (Object.prototype.hasOwnProperty.call(currentSkills[v], skillName)) {
              currentSkills[v][skillName]++
            } else {
              currentSkills[v][skillName] = 1
            }
            //console.log('SkillBreakdown.vue::createBreakdown:',key, _id, decorationData)
          }
        }
      })
      for (const [, obj] of Object.entries(currentSkills)) {
        for (const [_skill, _lv] of Object.entries(obj)) {
          if (_skill in tempSummary) {
            tempSummary[_skill] += _lv
          } else {
            tempSummary[_skill] = _lv
          }
        }
      }
      this.skills = currentSkills
      // Sort By Skill Level
      pairs = Object.entries(tempSummary)
      pairs.sort((a, b) => {
        // First, the skill names are ordered by asc.
        return a[0].localeCompare(b[0], 'ja')
      })
      pairs.sort((a, b) => {
        // Finaly, the skill levels are ordered by desc.
        return (a[1] < b[1] ? 1 : -1)
      })
      this.summary = Object.fromEntries(pairs)
      //console.log(this.summary, this.skills)
    },
  },

}
</script>