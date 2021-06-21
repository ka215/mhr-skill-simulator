<template>
  <v-card
    outlined
  >
    <v-list>
      <v-list-group
        v-model="expanded"
        no-action
      >
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title
              class="sub-headline--text"
            >{{ labels.title }}</v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-item-content
          class="px-4"
        >
          <v-list-item-subtitle
            :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-2': !$vuetify.theme.isDark}]"
          >{{ labels.subtitle }}</v-list-item-subtitle>
          <v-row
            class="mt-2"
          >
            <v-col
              class="col-6 col-md-2 offset-md-1"
            >
              <v-select
                v-model="t_rarity"
                :items="range(1, 7)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.rarity"
                dense
                outlined
                hide-details
              ></v-select>
            </v-col>
            <v-col
              class="col-6 col-md-2"
            >
              <v-text-field
                v-model="t_name"
                :label="labels.talismanName"
                :placeholder="labels.placeholder"
                dense
                outlined
                hide-details
                readonly
                disabled
              ></v-text-field>
            </v-col>
            <v-col
              class="col-4 col-md-2"
            >
              <v-select
                v-model="t_slot1"
                :items="range(0, 3)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.slot1"
                dense
                outlined
                hide-details
              ><template v-slot:item="{ item }">
                <Talisman :slotType="item" size="24" />
              </template></v-select>
            </v-col>
            <v-col
              class="col-4 col-md-2"
            >
              <v-select
                v-model="t_slot2"
                :items="range(0, 2)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.slot2"
                dense
                outlined
                hide-details
              ><template v-slot:item="{ item }">
                <Talisman :slotType="item" size="24" />
              </template></v-select>
            </v-col>
            <v-col
              class="col-4 col-md-2"
            >
              <v-select
                v-model="t_slot3"
                :items="range(0, 1)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.slot3"
                dense
                outlined
                hide-details
              ><template v-slot:item="{ item }">
                <Talisman :slotType="item" size="24" />
              </template></v-select>
            </v-col>
            <v-col
              class="col-8 col-md-3 offset-md-1"
            >
              <v-select
                v-model="t_skill1"
                :items="skillsAllowedTalisman(t_skill2)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.skill1"
                dense
                outlined
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col
              class="col-4 col-md-2"
            >
              <v-select
                v-model="t_skill1_level"
                :items="skillLevels(t_skill1)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.level"
                dense
                outlined
                hide-details
                :disabled="!t_skill1"
              ></v-select>
            </v-col>
            <v-col
              class="col-8 col-md-3"
            >
              <v-select
                v-model="t_skill2"
                :items="skillsAllowedTalisman(t_skill1)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.skill2"
                dense
                outlined
                hide-details
                clearable
              ></v-select>
            </v-col>
            <v-col
              class="col-4 col-md-2"
            >
              <v-select
                v-model="t_skill2_level"
                :items="skillLevels(t_skill2)"
                :menu-props="{bottom:true, offsetY:true}"
                :label="labels.level"
                dense
                outlined
                hide-details
                :disabled="!t_skill2"
              ></v-select>
            </v-col>
          </v-row>
        </v-list-item-content>

        <div class="text-center">
          <v-icon class="grey--text" dense>mdi-chevron-double-down</v-icon>
        </div>

        <v-list-item-content
          class="px-4"
        >
          <template v-if="$vuetify.breakpoint.name === 'xs'">
            <v-simple-table
              id="preview-talisman"
            >
              <thead>
                <tr>
                  <th colspan="2">{{ labels.worth }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td width="100">{{ t_worth || 0 }}</td>
                  <td>
                    <template v-if="t_evaluation > 0">
                      <v-rating
                        v-model="t_evaluation"
                        empty-icon="mdi-star"
                        background-color="grey darken-2"
                        color="amber accent-4"
                        length="6"
                        dense
                      ><template v-slot:item="props">
                        <v-icon
                          :color="starColor(props.isFilled, props.index)"
                        >mdi-star</v-icon>
                      </template></v-rating>
                    </template>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
          </template>
          <template v-else>
            <v-list-item-subtitle
              :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-2': !$vuetify.theme.isDark}]"
            >{{ labels.preview }}</v-list-item-subtitle>
            <v-simple-table
              id="preview-talisman"
            >
              <thead>
                <tr>
                  <template v-if="$vuetify.breakpoint.name !== 'sm'">
                    <th>{{ labels.talismanName }}</th>
                    <th>{{ labels.rarity }}</th>
                  </template>
                  <th>{{ labels.slots }}</th>
                  <th>{{ labels.skill1 }}</th>
                  <th>{{ labels.skill2 }}</th>
                  <th colspan="2">{{ labels.worth }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <template v-if="$vuetify.breakpoint.name !== 'sm'">
                    <td :class="['br-1', rarityClass]" width="200">{{ t_name || '-' }}</td>
                    <td :class="rarityClass" width="80">{{ t_rarity || '-' }}</td>
                  </template>
                  <td width="120">
                    <div class="d-flex justify-center">
                      <Talisman :slotType="t_slot1" size="24" />
                      <Talisman :slotType="t_slot2" size="24" />
                      <Talisman :slotType="t_slot3" size="24" />
                    </div>
                  </td>
                  <td width="260">
                    <div class="d-flex justify-start">
                      <span>{{ t_skill1 || '-' }}</span>
                      <div
                        v-if="t_skill1_level"
                        class="d-flex justify-start ml-5"
                      >
                        <v-icon
                          v-for="n of t_skill1_level"
                          :key="`skill1-${n}`"
                          dense
                          :class="$vuetify.theme.isDark ? 'cyan--text text--darken-1 ml-n2': 'cyan--text ml-n2'"
                        >mdi-label-variant</v-icon>
                        <template v-if="skillMaxLevel(t_skill1) > t_skill1_level">
                          <v-icon
                            v-for="n of (skillMaxLevel(t_skill1) - t_skill1_level)"
                            :key="`skill1-remain${n}`"
                            dense
                            :class="$vuetify.theme.isDark ? 'grey--text text--darken-2 ml-n2': 'grey--text ml-n2'"
                          >mdi-label-variant</v-icon>
                        </template>
                      </div>
                    </div>
                  </td>
                  <td width="260">
                    <div class="d-flex justify-start">
                      <span>{{ t_skill2 || '-' }}</span>
                      <div
                        v-if="t_skill2_level"
                        class="d-flex justify-start ml-5"
                      >
                        <v-icon
                          v-for="n of t_skill2_level"
                          :key="`skill2-${n}`"
                          dense
                          :class="$vuetify.theme.isDark ? 'cyan--text text--darken-1 ml-n2': 'cyan--text ml-n2'"
                        >mdi-label-variant</v-icon>
                        <template v-if="skillMaxLevel(t_skill2) > t_skill2_level">
                          <v-icon
                            v-for="n of (skillMaxLevel(t_skill2) - t_skill2_level)"
                            :key="`skill2-remain${n}`"
                            dense
                            :class="$vuetify.theme.isDark ? 'grey--text text--darken-2 ml-n2': 'grey--text ml-n2'"
                          >mdi-label-variant</v-icon>
                        </template>
                      </div>
                    </div>
                  </td>
                  <td width="100">{{ t_worth || 0 }}</td>
                  <td>
                    <template v-if="t_evaluation > 0">
                      <StarRating :evaluation="t_evaluation" />
                    </template>
                  </td>
                </tr>
              </tbody>
            </v-simple-table>
          </template>
        </v-list-item-content>

        <v-divider />

        <v-card-actions
          class="d-flex"
        >
          <v-spacer />
          <v-btn
            class="btn-default"
            :disabled="!readyToGo()"
            @click="resetTlisman"
          >{{ labels.reset }}</v-btn>
          <v-btn
            class="btn-primary"
            :disabled="!readyToGo()"
            @click="registerData()"
          >{{ labels.register }}</v-btn>
        </v-card-actions>
      </v-list-group>
    </v-list>
  </v-card>
</template>

<style lang="scss">
#preview-talisman table tbody tr:hover { background-color: transparent !important; }
</style>

<script>
import Talisman from '@/components/Talisman'
import StarRating from '@/components/StarRating'

export default {
  name: 'TalismanRegister',

  components: {
    Talisman,
    StarRating,
  },

  data: () => ({
    expanded: true,
    t_rarity: null,
    t_name: null,
    t_slot1: 0,
    t_slot2: 0,
    t_slot3: 0,
    t_skill1: null,
    t_skill2: null,
    t_skill1_level: null,
    t_skill2_level: null,
    t_worth: 0,
    t_evaluation: 0,
    labels: {
      title: '護石の登録',//'Register New Talisman',
      subtitle: '登録した護石は装備品として扱うことができます。',//'By registering your talisman here, you can treat it as equipment in the simulator.',
      rarity: 'レア度',//'Rarity',
      talismanName: '護石名',//'Talisman Name',
      placeholder: 'レア度を選択してください',//'Choose Rarity',
      slot1: 'スロット１',//'Slot 1',
      slot2: 'スロット２',//'Slot 2',
      slot3: 'スロット３',//'Slot 3',
      slots: 'スロット',//'Slots',
      skill1: 'スキル１',//'Skill 1',
      skill2: 'スキル２',//'Skill 2',
      preview: '護石登録前プレビュー',//'Preview before registering talisman',
      level: 'レベル',//'Skill Level',
      worth: '護石評価',//'Worth',
      reset: 'やり直す',//'Clear',
      register: '登録する',//'Regist',
    },
  }),

  created() {
    //
  },

  watch: {
    t_rarity: function(value) {
      switch (value) {
        case 1:  this.t_name = '凪の護石'; break
        case 2:  this.t_name = '風立の護石'; break
        case 3:  this.t_name = '烈風の護石'; break
        case 4:  this.t_name = '鬼雨の護石'; break
        case 5:  this.t_name = '山嵐の護石'; break
        case 6:  this.t_name = '天雷の護石'; break
        case 7:  this.t_name = '神嵐の護石'; break
        default: this.t_name = null; break
      }
    },
    t_slot1: function() {
      this.calculateWorth()
    },
    t_slot2: function() {
      this.calculateWorth()
    },
    t_slot3: function() {
      this.calculateWorth()
    },
    t_skill1: function() {
      this.t_skill1_level = null
    },
    t_skill2: function() {
      this.t_skill2_level = null
    },
    t_skill1_level: function() {
      this.calculateWorth()
    },
    t_skill2_level: function() {
      this.calculateWorth()
    },
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'addData':
            //console.log('TalismanRegister.vue::$store.subscribeAction.addData:', action, action.payload.data.id)
            this.resetTlisman()
            break
        }
      }
    })
  },

  computed: {
    rarityClass: function() {
      return `rare-${this.t_rarity}--text`
    },
  },

  methods: {
    range: function(start, end) {
      return Array.from({ length: (end - start) + 1}, (_, i) => start + i)
    },
    skillsAllowedTalisman: function(addExcludeSkill) {
      let allowedSkills = this.$store.state.skills.map(item => item.name),
          excludeSkills = ['炎鱗の恩恵', '霞皮の恩恵', '鋼殻の恩恵', '剥ぎ取り名人', '風紋の一致', '捕獲名人', '弓溜め段階解放', '雷紋の一致', '風雷合一', '龍気活性']
      if (addExcludeSkill) {
        excludeSkills.push(addExcludeSkill)
      }
      excludeSkills.forEach(elm => {
        let i = allowedSkills.findIndex(v => v === elm)
        allowedSkills.splice(i, 1)
      })
      return allowedSkills
    },
    skillMaxLevel: function(skill) {
      let skillData = this.$store.state.skills.find(item => item.name === skill),
          max_lv = 1
      for (let key in skillData) {
        if (key === 'max_lv') {
          max_lv = skillData[key]
        }
      }
      return max_lv
    },
    skillLevels: function(skill) {
      let max_lv = this.skillMaxLevel(skill)
      max_lv = max_lv > 3 ? 3: max_lv
      return this.range(1, max_lv)
    },
    resetTlisman: function() {
      this.t_rarity = null
      this.t_name   = null,
      this.t_slot1  = 0
      this.t_slot2  = 0
      this.t_slot3  = 0
      this.t_skill1 = null
      this.t_skill2 = null
      this.t_skill1_level = null
      this.t_skill2_level = null
      this.t_worth = 0
    },
    readyToGo: function() {
      return this.t_rarity != null && this.t_worth > 0
    },
    registerData: function() {
      let newTalisman = {
        name: this.t_name,
        rarity: this.t_rarity,
        slot1: this.t_slot1,
        slot2: this.t_slot2,
        slot3: this.t_slot3,
        skills: {},
        worth: this.t_worth,
      }
      if (this.t_skill1 != null && this.t_skill1_level != null) {
        newTalisman.skills[this.t_skill1] = this.t_skill1_level
      }
      if (this.t_skill2 != null && this.t_skill2_level != null) {
        newTalisman.skills[this.t_skill2] = this.t_skill2_level
      }
      this.saveData('post', {table: 'talismans', data: newTalisman}, (response) => {
        let notices = {
          title: null,
          messages: [],
        }
        if (response.data.state == 201) {
          newTalisman.id = response.data.id
          // Save into cache storage
          let request = /*`talisman/${response.data.id}`,*/`index.php?tbl=talismans&filters[id]=${response.data.id}`,
              cacheContent = newTalisman
          this.$userCache.save(request, response, cacheContent)
          this.$store.dispatch('addData', {property: 'talismans', data: newTalisman})
          notices.title = '通知'
          notices.messages = [ '護石を登録しました。', '登録された護石は護石管理から確認・削除できます。' ]
        } else {
          notices.title = 'エラー'
          notices.messages = [ '護石の登録に失敗しました。', 'もう一度お試しください。' ]
        }
        //console.log('After talisman insertion:', result, notices)
        this.$root.$emit('open:notification', notices)
        //}, (response) => {
        //console.log('saveData::interceptors:', response)
      })
    },
    calculateWorth: function() {
      if (!((this.t_skill1 && this.t_skill1_level) || (this.t_skill2 && this.t_skill2_level))) {
        this.t_worth = 0
        this.t_evaluation = 0
        return
      }
      let skill_evaluation = this.$store.state.skill_evaluation,
          slot_base_worth  = 8.33 * 1.6491,
          evaluations = [ 1 ],
          slots = [ this.t_slot1, this.t_slot2, this.t_slot3 ],
          _se, _bonus, _ev
      slots.forEach(v => {
        switch (v) {
          case 3:  evaluations.push(slot_base_worth * 1.37); break
          case 2:  evaluations.push(slot_base_worth * 1.21); break
          case 1:  evaluations.push(slot_base_worth * 1.06); break
          default: break
        }
      })
      if (this.t_skill1) {
        _se = skill_evaluation.filter(elm => elm.name === this.t_skill1)
        _bonus = this.skillMaxLevel(this.t_skill1) == this.t_skill1_level ? 1.1 : 1
        _ev = (_se[0].evaluation / 100) * (1 + (_se[0].slot - 1) / 10) * this.t_skill1_level * _bonus
        evaluations.push(_ev)
      }
      if (this.t_skill2) {
        _se = skill_evaluation.filter(elm => elm.name === this.t_skill2)
        _bonus = this.skillMaxLevel(this.t_skill2) == this.t_skill2_level ? 1.1 : 1
        _ev = (_se[0].evaluation / 100) * (1 + (_se[0].slot - 1) / 10) * this.t_skill2_level * _bonus
        evaluations.push(_ev)
      }
      this.t_worth = Math.round(evaluations.reduce((acc, cur) => acc + cur, 0) * 100) / 100
      this.t_evaluation = Math.ceil(this.t_worth / 20)
    },
  },

}
</script>
