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
          <tr
            v-for="(lv, skill) in summary"
            :key="skill"
            @click="openDialog(skill, lv)"
          >
            <td>{{ skill }}</td>
            <td
              :class="levelTextClass(skill, lv)"
            >Lv{{ displayLevel(skill, lv) }}</td>
            <td class="bd-0"></td>
            <td
              v-for="n in 6"
              :key="n"
              :class="`bd-${n}`"
            >
              {{ getSkillLv(skill, n) }}
            </td>
          </tr>
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
    <template>
      <SkillDetail />
    </template>
  </v-card>
</template>

<script>
import SkillDetail from '@/components/SkillDetail'
import mockData from '@/../public/mock_data.json'

export default {
  name: 'SkillBreakdown',

  components: {
    SkillDetail,
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: 'スキルレベル内訳',
      th: [ '発動スキル', '武器', '頭部', '胸部', '腕部', '腰部', '脚部', '護石' ],
    },
    skills: null,
    summary: null,
  }),

  created() {
    const armors = mockData.armors,
          talismans = mockData.talismans
    this.skills = Object.assign(...armors.map((obj) => ({ [obj.part + 1]: obj.skills })))
    this.skills[6] = talismans.map((obj) => obj.skills).shift()
    let temp_summary = {}
    for (const [, obj] of Object.entries(this.skills)) {
      for (const [_k, _v] of Object.entries(obj)) {
          if (_k in temp_summary) {
            temp_summary[_k] += _v
          } else {
            temp_summary[_k] = _v
          }
      }
    }
    // Sort By Skill Level
    let pairs = Object.entries(temp_summary)
    pairs.sort(function(a, b) {
      // keys by asc
      let ak = a[0], bk = b[0]
      return (ak < bk ? -1 : 1)
    })
    pairs.sort(function(a, b) {
      // values by desc
      let av = a[1], bv = b[1]
      return (av < bv ? 1 : -1)
    })
    this.summary = Object.fromEntries(pairs)
    console.log('SkillBreakdown.vue::created', this.skills, this.summary)
    /* check
    for (let _k in this.summary) {
      console.log(_k, this.summary[_k])
    }
    */
  },

  mounted() {
    //
  },

  computed: {
    //
  },

  methods: {
    levelTextClass: function (skill, lv) {
      // dummy
      switch (lv) {
        case 4:  return 'excess-level--text'
        case 3:  return 'max-level--text'
        case 2:  return 'below-level--text'
        case 1:
          return /^剥ぎ取り/.test(skill) ? 'max-level--text' : 'below-level--text'
        default: return 'below-level--text'
      }
    },
    displayLevel: function (skill, lv) {
      // dummy
      if (lv > 3) {
        lv = 3
      }
      return lv
    },
    getSkillLv: function (skill, part) {
      if (skill in this.skills[part]) {
        return this.skills[part][skill]
      }
    },
    openDialog: function (skill, lv) {
      this.$root.$emit('open:SkillDetail', skill, lv)
    },
  },

}
</script>