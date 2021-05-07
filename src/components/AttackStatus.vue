<template>
  <v-list
    subheader
  >
    <v-subheader
      class="sub-headline--text"
    >{{ labels.title }}</v-subheader>
    <Parameter name="attack"   :baseValue="weapon.attack" />
    <Parameter name="element1" :baseValue="weapon.elem1_value" :elementName="elementName(weapon.element1)" />
    <Parameter name="element2" :baseValue="weapon.elem2_value" :elementName="elementName(weapon.element2)" />
    <Parameter name="affinity"  :baseValue="weapon.affinity" />
<!-- /* For Gunner
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 2em;"
          >
            <v-list-item-title
              class="col-10 pa-0"
            >
              <v-icon x-small>mdi-rhombus</v-icon>
              ブレ
            </v-list-item-title>
            <div
              class="col-2 pa-0 text-right"
            >なし</div>
          </v-list-item>
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 1.5em;"
            dense
          >
            <v-list-item-title
              class="col-10 pa-0 text-right grey--text"
            >
              スキルによる補正
            </v-list-item-title>
            <v-list-item-title
              class="col-2 pa-0 text-right grey--text"
            >±0</v-list-item-title>
          </v-list-item>
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 2em;"
          >
            <v-list-item-title
              class="col-10 pa-0"
            >
              <v-icon x-small>mdi-rhombus</v-icon>
              反動
            </v-list-item-title>
            <div
              class="col-2 pa-0 text-right"
            >極小</div>
          </v-list-item>
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 1.5em;"
            dense
          >
            <v-list-item-title
              class="col-10 pa-0 text-right grey--text"
            >
              スキルによる補正
            </v-list-item-title>
            <v-list-item-title
              class="col-2 pa-0 text-right grey--text"
            >±0</v-list-item-title>
          </v-list-item>
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 2em;"
          >
            <v-list-item-title
              class="col-10 pa-0"
            >
              <v-icon x-small>mdi-rhombus</v-icon>
              リロード
            </v-list-item-title>
            <div
              class="col-2 pa-0 text-right"
            >極速</div>
          </v-list-item>
          <v-list-item
            class="d-flex justify-space-between ma-0 px-2 py-0"
            style="min-height: 1.5em;"
            dense
          >
            <v-list-item-title
              class="col-10 pa-0 text-right grey--text"
            >
              スキルによる補正
            </v-list-item-title>
            <v-list-item-title
              class="col-2 pa-0 text-right grey--text"
            >±0</v-list-item-title>
          </v-list-item>
*/ -->
  </v-list>
</template>

<script>
import Parameter from '@/components/Parameter'
import mockData from '@/../public/mock_data.json'

export default {
  name: 'AttackStatus',

  components: {
    Parameter,
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: '攻撃ステータス',
      elements: [ '', '火', '水', '雷', '氷', '龍', '毒', '麻痺', '睡眠', '爆破' ],
    },
    weapon: null,
  }),

  created() {
    this.weapon = mockData.weapons[0]
  },

  mounted() {
    //
  },

  filters: {
    //
  },

  computed: {
    //
  },

  methods: {
    elementName: function(value) {
      return this.labels.elements[value]
    }
  },

}
</script>