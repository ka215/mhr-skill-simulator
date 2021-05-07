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
            なし
          </template>
          <template v-else-if="$props.name === 'element2' && !$props.elementName">
          </template>
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
          <template v-if="$props.name === 'affinity'">%</template>
        </template>
      </div>
    </v-list-item>
    <template
      v-if="!/^element(1|2)$/.test($props.name) || $props.baseValue != 0"
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
      default: 0
    },
    elementName: {
      Type: String,
      default: null
    },
  },

  data: () => ({
    labels: {
      title: null,
      attack:   '攻撃力',
      element1: '属性',
      element2: '',
      affinity: '会心率',
      defense:  '防御力',
      fire:     '火耐性',
      water:    '水耐性',
      thunder:  '雷耐性',
      ice:      '氷耐性',
      dragon:   '龍耐性',
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
    //
  },

  mounted() {
    this.$root.$on(`update:${this.$props.name}BySkill`, (value) => {
      console.log(`Parameter.vue::update:${this.$props.name}BySkill`, value)
      this.correctedValues.skill = value
    })
    if (/^defense$/.test(this.$props.name)) {
      this.$root.$on('update:defenseBonus', (value) => {
        console.log('Parameter.vue::update:defenseBonus', value)
        this.correctedValues.bonus = value
      })
      this.$root.$on('update:defenseLevel', (...args) => {
        const [lv, part] = args,
              parts = ['head', 'chest', 'arm', 'waist', 'leg', ]
        //console.log('Parameter.vue::update:defenseLevel', args, lv, parts[part])
        this.correctedValues[parts[part]] = (lv - 1) * 2
      })
    }
    if (/^(attack|defense)$/.test(this.$props.name)) {
      this.$root.$on(`update:${this.$props.name}ByItem`, (value) => {
        console.log(`Parameter.vue::update:${this.$props.name}ByItem`, value)
        this.correctedValues.item = value
      })
    }
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
      return parseInt(this.$props.baseValue, 10) + Object.values(this.correctedValues).reduce((acc, cur) => acc + cur)
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