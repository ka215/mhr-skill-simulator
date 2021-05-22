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
        class="col-2 pa-0 text-right"
      >
        <template v-if="!/^element(1|2)$/.test($props.name) || $props.baseValue != 0">
          {{ fixedValue }}
          <template v-if="$props.name === 'affinity'">
            <v-icon x-small class="text--secondary">mdi-percent-outline</v-icon>
          </template>
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
        <v-list-item-title
          :class="correctedLabelClass(correctedValues.skill)"
        >{{ labels.correctedSkill }}</v-list-item-title>
        <v-list-item-title
          :class="correctedValueClass(correctedValues.skill)"
        >{{ correctedValues.skill | dispCorrect }}</v-list-item-title>
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
      correctedSkill: 'スキルによる補正',
      correctedBonus: '武器の防御力ボーナス',
      correctedItem:  'アイテム効果',
    },
    elementIcons: {
      fire:    'mdi-fire',
      water:   'mdi-water',
      thunder: 'mdi-flash',
      ice:     'mdi-snowflake',
      dragon:  'mdi-khanda',
    },
    correctedValues: {
      skill: 0,
      item:  0,
      bonus: 0,
      head:  0,
      chest: 0,
      arm:   0,
      waist: 0,
      leg:   0,
    },
  }),

  created() {
    if (this.$store.getters.equipmentExists('weapon')) {
      this.correctedValues.bonus = 0
      this.correctedValues.bonus += this.$store.getters.equipmentKindOf('weapon', 'defense_bonus')
    }
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
            if (action.payload.property === 'weapon') {
              this.correctedValues.bonus = 0
              this.correctedValues.bonus += this.$store.getters.equipmentKindOf('weapon', 'defense_bonus')
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

  computed: {
    fixedValue () {
      if (this.$props.baseValue != null) {
        return parseInt(this.$props.baseValue, 10) + Object.values(this.correctedValues).reduce((acc, cur) => acc + cur)
      } else {
        return '-'
      }
    },
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
  },

}
</script>