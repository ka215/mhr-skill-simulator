<template>
  <v-list-item-content
    class="py-0"
  >
    <v-list-item
      class="d-flex justify-space-between ma-0 px-2 py-0"
      style="min-height: 2em;"
    >
      <template
        v-if="/^element(1|2)$/.test($props.name)"
      >
        <v-list-item-title
          class="col-4 pa-0"
        >
          <template
            v-if="$props.name === 'element1'"
          >
            <v-icon x-small>mdi-rhombus</v-icon>
            {{ labels[$props.name] }}
          </template>
        </v-list-item-title>
        <div
          class="col-6 pa-0 text-right"
        >
          <template v-if="$props.name === 'element1' && !$props.elementName">
            {{ labels.noElement }}
          </template>
          <template v-else-if="$props.name === 'element2' && !$props.elementName"></template>
          <template v-else>
            {{ $props.elementName }}属性
          </template>
        </div>
      </template>
      <template
        v-else
      >
        <v-list-item-title
          class="col-10 pa-0"
        >
          <template
            v-if="/^(fire|water|thunder|ice|dragon)$/.test($props.name)"
          >
            <v-icon
              small
              :class="`${$props.name}--text`">
              {{ elementIcons[$props.name] }}
            </v-icon>
          </template>
          <template
            v-else
          >
            <v-icon x-small>mdi-rhombus</v-icon>
          </template>
          {{ labels[$props.name] }}
        </v-list-item-title>
      </template>
      <div
        _comment="Total Fixed Value"
        class="col-2 pa-0 text-right"
      >
        {{ fixedValue() }}
        <template v-if="$props.name === 'affinity'">
          <v-icon x-small class="text--secondary">mdi-percent-outline</v-icon>
        </template>
      </div>
    </v-list-item>
    <template
      v-if="!$props.noCorrected && (!/^element(1|2)$/.test($props.name) || $props.baseValue != 0)"
    >
      <v-list-item
        class="d-flex justify-space-between ma-0 px-2 py-0"
        style="min-height: 1.5em;"
        dense
      >
        <template v-if="stormsoulMagnification === 1">
          <v-list-item-title
            :class="correctedLabelClass(correctedValues.skill)"
          >{{ labels.correctedSkill }}</v-list-item-title>
          <v-list-item-title
            :class="correctedValueClass(correctedValues.skill)"
          >{{ correctedValues.skill | dispCorrect }}</v-list-item-title>
        </template>
        <template v-else>
          <v-list-item-title
            :class="correctedLabelClass(correctedValues.skill + correctedValues.stormsoul)"
          >{{ labels.correctedSkill }}</v-list-item-title>
          <v-list-item-title
            :class="correctedValueClass(correctedValues.skill + correctedValues.stormsoul)"
          >{{ (correctedValues.skill + correctedValues.stormsoul) | dispCorrect }}</v-list-item-title>
        </template>
      </v-list-item>
    </template>
    <template
      v-if="/^defense$/.test($props.name)"
    >
      <v-list-item
        class="d-flex justify-space-between ma-0 px-2 py-0"
        style="min-height: 1.5em;"
        dense
      >
        <v-list-item-title
          :class="correctedLabelClass(correctedValues.bonus)"
        >{{ labels.correctedBonus }}</v-list-item-title>
        <v-list-item-title
          :class="correctedValueClass(correctedValues.bonus)"
        >{{ correctedValues.bonus | dispCorrect }}</v-list-item-title>
      </v-list-item>
    </template>
    <template
      v-if="/^(attack|defense)$/.test($props.name)"
    >
      <v-list-item
        class="d-flex justify-space-between ma-0 px-2 py-0"
        style="min-height: 1.5em;"
        dense
      >
        <v-list-item-title
          :class="correctedLabelClass(correctedValues.item)"
        >{{ labels.correctedItem }}</v-list-item-title>
        <v-list-item-title
          :class="correctedValueClass(correctedValues.item)"
        >{{ correctedValues.item | dispCorrect }}</v-list-item-title>
      </v-list-item>
    </template>
    <template
      v-if="/^(fire|water|thunder|ice|dragon)$/.test($props.name)"
    >
      <v-list-item
        class="d-flex justify-space-between ma-0 px-2 py-0"
        style="min-height: 1.5em;"
        dense
      >
        <v-list-item-title
          :class="correctedLabelClass(correctedValues.series)"
        >{{ labels.correctedSeries }}</v-list-item-title>
        <v-list-item-title
          :class="correctedValueClass(correctedValues.series)"
        >{{ correctedValues.series | dispCorrect }}</v-list-item-title>
      </v-list-item>
    </template>
  </v-list-item-content>
</template>

<script>
export default {
  name: 'Parameter',

  components: {
    //
  },

  props: {
    name: {
      Type: String,
      default: null
    },
    baseValue: {
      Type: Number,
      default: null
    },
    elementName: {
      Type: String,
      default: null
    },
    noCorrected: {
      Type: Boolean,
      default: false
    },
  },

  data: () => ({
    labels: {
      title: null,
      attack:   '攻撃力',
      element1: '属性',
      element2: '',
      affinity: '会心率',
      defense_bonus: '防御力ボーナス',
      sharpness: '斬れ味',
      shelling_type: '砲撃タイプ',
      shelling_level: '砲撃レベル',
      melody_effects: '旋律効果',
      phial_type: '装着ビン', 
      phial_element: 'ビン属性',
      phial_element_value: 'ビン属性値',
      kinsect_level: '猟虫レベル',
      deviation: 'ブレ',
      recoil: '反動',
      reload: 'リロード',
      mods: 'パーツ',
      cluster_bomb_type: '拡散弾タイプ',
      special_ammo: '特殊弾',
      arc_shot: '曲射',
      charge_shot: '溜め攻撃',
      defense:  '防御力',
      fire:     '火耐性',
      water:    '水耐性',
      thunder:  '雷耐性',
      ice:      '氷耐性',
      dragon:   '龍耐性',
      noElement: 'なし',
      correctedSkill:  'スキルによる補正',
      correctedBonus:  '武器の防御力ボーナス',
      correctedItem:   'アイテム効果',
      correctedSeries: 'シリーズ耐性値ボーナス',
    },
    elementIcons: {
      fire:    'mdi-fire',
      water:   'mdi-water',
      thunder: 'mdi-flash',
      ice:     'mdi-snowflake',
      dragon:  'mdi-khanda',
    },
    correctedValues: {
      skill:  0,
      item:   0,
      bonus:  0,
      series: 0,
      stormsoul: 0,
    },
    armorSeries: {
      head:   null,
      chest:  null,
      arms:   null,
      waist:  null,
      legs:   null,
    },
    stormsoulMagnification: 1,
  }),

  created() {
    //console.log('Parameter.vue::created:', this.$store.getters.equipmentExists('weapon'), this.$store.getters.equipmentExists('armors'))
    if (this.$store.getters.equipmentExists('weapon')) {
      this.correctedValues.bonus = 0
      this.correctedValues.bonus += this.$store.getters.equipmentKindOf('weapon', 'defense_bonus')
    }
    if (this.$store.getters.equipmentExists('armors')) {
      let armorKinds = ['head', 'chest', 'arms', 'waist', 'legs']
      armorKinds.forEach(kind => {
        let seriesName = this.$store.getters.equipmentKindOf(kind, 'series')
        if (seriesName) {
          this.armorSeries[kind] = seriesName
        }
      })
      this.correctedValues.series = 0
      this.correctedValues.series += this.getCorrectedBySeries()
    }
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
            if (action.payload.property === 'weapon') {
              // When changing weapons
              this.correctedValues.bonus = 0
              this.correctedValues.bonus += this.$store.getters.equipmentKindOf('weapon', 'defense_bonus') || 0
            } else
            if (action.payload.property !== 'talisman') {
              // When changing armors
              this.armorSeries[action.payload.property] = this.$store.getters.equipmentKindOf(action.payload.property, 'series')
              this.correctedValues.series = 0
              this.correctedValues.series += this.getCorrectedBySeries()
            }
            break
          case 'setAggSkills':
            // When the aggregated skills are changed
            this.correctedValues.stormsoul = 0
            this.stormsoulMagnification = 1
            if (Object.keys(action.payload.aggrigation).length > 0) {
              this.correctedValues.skill = this.getCorrectedBySkills(action.payload.aggrigation, this.$props)
            } else {
              this.correctedValues.skill = 0
            }
            break
          case 'setPlayerData':
            if (action.payload.property === 'items' && /^(attack|defense)$/.test(this.$props.name)) {
              this.correctedValues.item = 0
              let items = action.payload.value.filter(item => item.target === this.$props.name)
              if (items.length > 0) {
                //console.log('Parameter.vue::$store.subscribeAction:setPlayerData:', action.payload.value, this.$props.name, items)
                this.correctedValues.item += items.map(elm => elm.value).reduce((acc, cur) => acc + cur, 0)
              }
            }
            break
        }
      }
    })
  },

  mounted() {
    //console.log('Parameter.vue::mouted:', this.$store.getters.equipmentExists('weapon'), this.$store.getters.equipmentExists('armors'))
  },

  filters: {
    dispCorrect (value) {
      if (value > 0) {
        return `+${value}`
      } else if (value < 0) {
        return `-${value}`
      } else {
        return `±${value}`
      }
    }
  },

  methods: {
    correctedLabelClass: (value) => {
      return {
        'col-10': true, 'pa-0': true, 'text-right': true,
        'decrease--text': value < 0,
        'nochange--text': value == 0,
        'increase--text': value > 0,
      }
    },
    correctedValueClass: (value) => {
      return {
        'col-2': true, 'pa-0': true, 'text-right': true,
        'decrease--text': value < 0,
        'nochange--text': value == 0,
        'increase--text': value > 0,
      }
    },
    getCorrectedBySeries: function() {
      const armorKinds = ['head', 'chest', 'arms', 'waist', 'legs']
      let countBySeries = armorKinds.map(part => this.armorSeries[part]).reduce((acc, cur) => {
        let key = cur == null ? 'none': cur
        acc[key] = acc[key] ? acc[key] + 1: 1
        return acc
      }, { none: 0 })
      let countSeries = 0
      for (let [key, value] of Object.entries(countBySeries)) {
        if (key !== 'none' && value >= 3) {
          countSeries = value
        }
      }
      switch (countSeries) {
        case 3:  return 1
        case 4:  return 2
        case 5:  return 3
        default: return 0
      }
    },
    getCorrectedBySkills: function(allSkills, args) {
      let bv = args.baseValue,
          cv = 0, lv = 0
      switch (args.name) {
        case 'attack':
          if (Object.prototype.hasOwnProperty.call(allSkills, '攻撃')) {
            lv = allSkills['攻撃'] > 7 ? 7: allSkills['攻撃']
            switch (lv) {
              case 1: cv += 3; break
              case 2: cv += 6; break
              case 3: cv += 9; break
              case 4: cv += Math.floor(bv * 0.05) + 7; break
              case 5: cv += Math.floor(bv * 0.06) + 8; break
              case 6: cv += Math.floor(bv * 0.08) + 9; break
              case 7: cv += Math.floor(bv * 0.10) + 10; break
            }
          }
          break
        case 'element1':
        case 'element2':
          if (Object.prototype.hasOwnProperty.call(allSkills, `${args.elementName}属性攻撃強化`)) {
            lv = allSkills[`${args.elementName}属性攻撃強化`] > 5 ? 5: allSkills[`${args.elementName}属性攻撃強化`]
            switch (lv) {
              case 1: cv += 2; break
              case 2: cv += 3; break
              case 3: cv += Math.floor(bv * 0.05) + 4; break
              case 4: cv += Math.floor(bv * 0.10) + 4; break
              case 5: cv += Math.floor(bv * 0.20) + 4; break
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, `${args.elementName}属性強化`)) {
            lv = allSkills[`${args.elementName}属性強化`] > 3 ? 3: allSkills[`${args.elementName}属性強化`]
            switch (lv) {
              case 1: cv += Math.floor(bv * 0.05) + 1; break
              case 2: cv += Math.floor(bv * 0.10) + 2; break
              case 3: cv += Math.floor(bv * 0.20) + 5; break
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '鋼殻の恩恵')) {
            if (args.elementName === '水' || args.elementName === '氷') {
              lv = allSkills['鋼殻の恩恵'] > 4 ? 4: allSkills['鋼殻の恩恵']
              switch (lv) {
                case 1: cv += Math.floor(bv * 0.05); break
                case 2: cv += Math.floor(bv * 0.10); break
                case 3: cv += Math.floor(bv * 0.10); break
                case 4: cv += Math.floor(bv * 0.10); break
              }
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '炎鱗の恩恵')) {
            if (args.elementName === '火' || args.elementName === '爆破') {
              lv = allSkills['炎鱗の恩恵'] > 4 ? 4: allSkills['炎鱗の恩恵']
              switch (lv) {
                case 1: cv += Math.floor(bv * 0.05); break
                case 2: cv += Math.floor(bv * 0.10); break
                case 3: cv += Math.floor(bv * 0.10); break
                case 4: cv += Math.floor(bv * 0.10); break
              }
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '風雷合一')) {
            if (args.elementName === '雷' || args.elementName === '龍') {
              lv = allSkills['風雷合一'] > 5 ? 5: allSkills['風雷合一']
              switch (lv) {
                case 1: this.stormsoulMagnification = 1.05; break
                case 2: this.stormsoulMagnification = 1.10; break
                case 3: this.stormsoulMagnification = 1.15; break
                case 4: this.stormsoulMagnification = 1.15; break
                case 5: this.stormsoulMagnification = 1.15; break
              }
            } else {
              this.stormsoulMagnification = 1
              this.correctedValues.stormsoul = 0
            }
          }
          break
        case 'affinity':
          if (Object.prototype.hasOwnProperty.call(allSkills, '見切り')) {
            lv = allSkills['見切り'] > 7 ? 7: allSkills['見切り']
            switch (lv) {
              case 1: cv +=  5; break
              case 2: cv += 10; break
              case 3: cv += 15; break
              case 4: cv += 20; break
              case 5: cv += 25; break
              case 6: cv += 30; break
              case 7: cv += 40; break
            }
          }
          break
        case 'sharpness':
          if (Object.prototype.hasOwnProperty.call(allSkills, '匠')) {
            lv = allSkills['匠'] > 5 ? 5: allSkills['匠']
            switch (lv) {
              case 1: cv += 10; break
              case 2: cv += 20; break
              case 3: cv += 30; break
              case 4: cv += 40; break
              case 5: cv += 50; break
            }
          }
          break
        case 'defense':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            switch (lv) {
              case 1: cv += 5; break
              case 2: cv += 10; break
              case 3: cv += Math.floor(bv * 0.05) + 10; break
              case 4: cv += Math.floor(bv * 0.05) + 20; break
              case 5: cv += Math.floor(bv * 0.08) + 20; break
              case 6: cv += Math.floor(bv * 0.08) + 35; break
              case 7: cv += Math.floor(bv * 0.10) + 35; break
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '火耐性')) {
            lv = allSkills['火耐性'] > 3 ? 3: allSkills['火耐性']
            cv += lv == 3 ? 10: 0
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '水耐性')) {
            lv = allSkills['水耐性'] > 3 ? 3: allSkills['水耐性']
            cv += lv == 3 ? 10: 0
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '雷耐性')) {
            lv = allSkills['雷耐性'] > 3 ? 3: allSkills['雷耐性']
            cv += lv == 3 ? 10: 0
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '氷耐性')) {
            lv = allSkills['氷耐性'] > 3 ? 3: allSkills['氷耐性']
            cv += lv == 3 ? 10: 0
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '龍耐性')) {
            lv = allSkills['龍耐性'] > 3 ? 3: allSkills['龍耐性']
            cv += lv == 3 ? 10: 0
          }
          break
        case 'fire':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            cv += (lv == 4 || lv == 5) ? 3 : ((lv == 6 || lv == 7) ? 5 : 0)
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '火耐性')) {
            lv = allSkills['火耐性'] > 3 ? 3: allSkills['火耐性']
            switch (lv) {
              case 1: cv += 6; break
              case 2: cv += 12; break
              case 3: cv += 20; break
            }
          }
          break
        case 'water':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            cv += (lv == 4 || lv == 5) ? 3 : ((lv == 6 || lv == 7) ? 5 : 0)
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '水耐性')) {
            lv = allSkills['水耐性'] > 3 ? 3: allSkills['水耐性']
            switch (lv) {
              case 1: cv += 6; break
              case 2: cv += 12; break
              case 3: cv += 20; break
            }
          }
          break
        case 'thunder':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            cv += (lv == 4 || lv == 5) ? 3 : ((lv == 6 || lv == 7) ? 5 : 0)
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '雷耐性')) {
            lv = allSkills['雷耐性'] > 3 ? 3: allSkills['雷耐性']
            switch (lv) {
              case 1: cv += 6; break
              case 2: cv += 12; break
              case 3: cv += 20; break
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '雷紋の一致')) {
            lv = allSkills['雷紋の一致'] > 5 ? 5: allSkills['雷紋の一致']
            switch (lv) {
              case 1: cv += 1; break
              case 2: cv += 2; break
              case 3: cv += 3; break
              case 4: cv += 4; break
              case 5: cv += 4; break
            }
          }
          break
        case 'ice':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            cv += (lv == 4 || lv == 5) ? 3 : ((lv == 6 || lv == 7) ? 5 : 0)
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '氷耐性')) {
            lv = allSkills['氷耐性'] > 3 ? 3: allSkills['氷耐性']
            switch (lv) {
              case 1: cv += 6; break
              case 2: cv += 12; break
              case 3: cv += 20; break
            }
          }
          break
        case 'dragon':
          if (Object.prototype.hasOwnProperty.call(allSkills, '防御')) {
            lv = allSkills['防御'] > 7 ? 7: allSkills['防御']
            cv += (lv == 4 || lv == 5) ? 3 : ((lv == 6 || lv == 7) ? 5 : 0)
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '龍耐性')) {
            lv = allSkills['龍耐性'] > 3 ? 3: allSkills['龍耐性']
            switch (lv) {
              case 1: cv += 6; break
              case 2: cv += 12; break
              case 3: cv += 20; break
            }
          }
          if (Object.prototype.hasOwnProperty.call(allSkills, '風紋の一致')) {
            lv = allSkills['風紋の一致'] > 5 ? 5: allSkills['風紋の一致']
            switch (lv) {
              case 1: cv += 1; break
              case 2: cv += 2; break
              case 3: cv += 3; break
              case 4: cv += 4; break
              case 5: cv += 4; break
            }
          }
          break
      }
      /*
      if (cv > 0) {
        console.log('Parameter.vue::When changing decorations in slots:', allSkills, args.name, bv, cv)
      }
      */
      return cv
    },
    fixedValue: function() {
      if (this.$props.baseValue != null) {
        let totalValue = parseInt(this.$props.baseValue, 10)
        if (this.$props.noCorrected) {
          return totalValue
        }
        switch (this.$props.name) {
          case 'attack':
            totalValue += this.correctedValues.skill + this.correctedValues.item
            break
          case 'defense':
            totalValue += this.correctedValues.skill + this.correctedValues.item + this.correctedValues.bonus
            break
          case 'fire':
          case 'water':
          case 'thunder':
          case 'ice':
          case 'dragon':
            totalValue += this.correctedValues.skill + this.correctedValues.series
            break
          case 'element1':
          case 'element2':
            if (!this.$props.elementName) {
              totalValue = ''
            } else {
              if (this.$props.elementName === '雷' || this.$props.elementName === '龍') {
                let tempValue = totalValue + this.correctedValues.skill
                totalValue = Math.floor(tempValue * this.stormsoulMagnification)
                this.correctedValues.stormsoul = totalValue - tempValue
                //console.log('fixedValue::%s: %f * %d = %d (+%d)', this.$props.elementName, this.stormsoulMagnification, tempValue, totalValue, this.correctedValues.stormsoul)
              } else {
                totalValue += this.correctedValues.skill
              }
            }
            break
          case 'affinity':
            totalValue += this.correctedValues.skill
            if (totalValue > 100) {
              totalValue = 100
            }
            break
        }
        return totalValue
      } else {
        return '-'
      }
    },
  },

}
</script>