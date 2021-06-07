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
                <td>
                  <template v-if="correntedByStormsoul.includes(skill)">
                    <span class="increase--text">{{ skill }}</span>
                  </template>
                  <template v-else>{{ skill }}</template>
                </td>
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
    <v-row v-if="Object.keys(summary).length > 0" class="mt-4 pb-4">
      <v-col cols="12" class="d-flex justify-end ma-0 pb-0">
        <span class="text-caption text--secondary mr-1" v-html="labels.hint"></span>
      </v-col>
      <template v-if="correntedByStormsoul.length > 0">
        <v-col cols="12" class="d-flex justify-end pb-0">
          <span class="text-caption text--secondary mr-1" v-html="labels.hint2"></span>
        </v-col>
      </template>
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
      hint: 'スキルレベルが<span class="increase--text">緑色</span>で表示されているものは、装飾品によって付与・強化されているスキルです',
      hint2: 'スキル名が<span class="increase--text">緑色</span>で表示されているものは、「風雷合一」によって強化されているスキルです',
    },
    skills: null,
    summary: null,
    stormsoul: '風雷合一',
    correntedByStormsoul: [],
  }),

  watch: {
    summary: function(value) {
      console.log('SkillBreakdown.vue::watch.summary:', value)
      this.$store.dispatch('setAggSkills', {aggrigation: value})
    }
  },

  created() {
    if (this.$store.getters.equipmentExists) {
      this.createBreakdown()
    }
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
          case 'setArmorLevel':
            this.createBreakdown()
            break
        }
      }
    })
  },

  mounted() {
    //
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
      //console.log('levelTextClass', skill)
      let max_lv = this.getMaxSkillLv(skill)
      switch (true) {
        case lv > max_lv:   return 'excess-level--text'
        case lv == max_lv:  return 'max-level--text'
        case lv < max_lv:   return 'below-level--text'
      }
    },
    displayLevel: function (skill, lv) {
      //console.log('displayLevel', skill)
      let max_lv = this.getMaxSkillLv(skill)
      if (lv > max_lv) {
        lv = max_lv
      }
      return lv
    },
    classInSlot: function (skill, part) {
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']
      //console.log('classInSlot', skill, this.skills[kinds[part]])
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
      //console.log('getSkillLv', skill)
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']
      if (skill in this.skills[kinds[part]]) {
        return this.skills[kinds[part]][skill]
      }
    },
    getMaxSkillLv: function (skill) {
      //console.log('getMaxSkillLv', skill)
      let targetSkill = this.$store.state.skills.find(row => row.name === skill)
      return targetSkill.max_lv
    },
    openDialog: function (skill, lv) {
      //console.log('openDialog', skill)
      this.$root.$emit('open:SkillDetail', skill, lv)
    },
    createBreakdown: function() {
      let kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'],
          armorKinds = ['head', 'chest', 'arms', 'waist', 'legs'],
          armorBuiltinSkills = [],
          currentSkills = {},
          tempSummary = {},
          pairs
      //console.log('SkillBreakdown.vue::setEquipment.after: Changed "%s"', kind)
      kinds.forEach(v => {
        // Get builtin skills from equipment item
        currentSkills[v] = Object.prototype.hasOwnProperty.call(this.$store.state[v].data, 'skills') ? Object.assign({}, this.$store.state[v].data.skills): {}
        if (this.isArmor(v)) {
          armorBuiltinSkills = this.arrayUnique(armorBuiltinSkills.concat(Object.keys(currentSkills[v])))
        }
        // Get skills from decorations attatched slots
        if (Object.keys(this.$store.state[v].slots).length > 0) {
          //console.log('!!:', this.$store.state[v].slots)
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
      // Chaeck the activation of the "Stormsoul" skill
      // (: スキル「風雷合一」の発動チェック
      armorKinds.forEach(kind => {
        if (Object.keys(currentSkills[kind]).includes(this.stormsoul)) {
          let armorLv = this.$store.getters.armorLevelKindOf(kind)
          if (armorLv >= 9) {
            currentSkills[kind][this.stormsoul] = 1
          } else {
            delete currentSkills[kind][this.stormsoul]
          }
          //console.log('風雷合一の有効化判定::%s Lv:%d => %s', kind, armorLv, (armorLv >= 9 ? '発動': '未発動'))
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
      // Skill level correction by the skill "Stormsoul"
      // (: スキル「風雷合一」のレベル補正
      this.correntedByStormsoul = []
      if (Object.prototype.hasOwnProperty.call(tempSummary, this.stormsoul) && tempSummary[this.stormsoul] >= 4) {
        for (let [_skill, ] of Object.entries(tempSummary)) {
          if (_skill !== this.stormsoul && armorBuiltinSkills.includes(_skill)) {
            this.correntedByStormsoul.push(_skill)
            switch (tempSummary[this.stormsoul]) {
              case 4:
                //console.log('スキル「風雷合一」のレベル: %d -> スキル「%s」補正: +1', tempSummary[this.stormsoul], _skill)
                tempSummary[_skill] += 1
                break
              case 5:
                //console.log('スキル「風雷合一」のレベル: %d -> スキル「%s」補正: +2', tempSummary[this.stormsoul], _skill)
                tempSummary[_skill] += 2
                break
            }
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