<template>
  <v-list
    subheader
  >
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
      <v-list-item-content>
        <Parameter name="attack"    :baseValue="weapon.attack" />
        <Parameter name="element1"  :baseValue="weapon.elem1_value" :elementName="elementName(weapon.element1)" />
        <Parameter name="element2"  :baseValue="weapon.elem2_value" :elementName="elementName(weapon.element2)" />
        <Parameter name="affinity"  :baseValue="weapon.affinity" />
        <template v-if="weapon.type < 11"><!-- /* 近接武器 */ -->
          <Parameter name="sharpness"  :baseValue="weapon.sharpness" noCorrected="false" />
        </template>
        <template v-if="weapon.type == 5"><!-- /* 狩猟笛 */ -->
          <Parameter name="melody_effects"  :baseValue="weapon.melody_effects" noCorrected="true" />
        </template>
        <template v-if="weapon.type == 7"><!-- /* ガンランス */ -->
          <Parameter name="shelling_type"  :baseValue="weapon.shelling_type" noCorrected="true" />
          <Parameter name="shelling_level"  :baseValue="weapon.shelling_level" noCorrected="true" />
        </template>
        <template v-if="weapon.type == 8 || weapon.type == 9"><!-- /* スラアク|チャアク */ -->
          <Parameter name="phial_type"  :baseValue="weapon.phial_type" noCorrected="true" />
          <Parameter name="phial_element"  :baseValue="weapon.phial_element_value" :elementName="elementName(weapon.phial_element)" noCorrected="true" />
        </template>
        <template v-if="weapon.type == 10"><!-- /* 操虫棍 */ -->
          <Parameter name="kinsect_level"  :baseValue="weapon.kinsect_level" noCorrected="true" />
        </template>
        <template v-if="weapon.type == 11 || weapon.type == 12"><!-- /* ボウガン系 */ -->
          <Parameter name="deviation"  :baseValue="weapon.deviation" noCorrected="true" />
          <Parameter name="recoil"  :baseValue="weapon.recoil" noCorrected="true" />
          <Parameter name="reload"  :baseValue="weapon.reload" noCorrected="true" />
          <Parameter name="mods"  :baseValue="weapon.mods" noCorrected="true" />
          <Parameter name="cluster_bomb_type"  :baseValue="weapon.cluster_bomb_type" noCorrected="true" />
          <Parameter name="special_ammo"  :baseValue="weapon.special_ammo" noCorrected="true" />
        </template>
        <template v-if="weapon.type == 13"><!-- /* 弓 */ -->
          <Parameter name="arc_shot"  :baseValue="weapon.arc_shot" noCorrected="true" />
          <Parameter name="charge_shot"  :baseValue="weapon.charge_shot" noCorrected="true" />
        </template>
      </v-list-item-content>
    </v-list-group>
  </v-list>
</template>

<script>
import Parameter from '@/components/Parameter'

export default {
  name: 'AttackStatus',

  components: {
    Parameter,
  },

  data: () => ({
    labels: {
      title: '攻撃ステータス',
      elements: [ '', '火', '水', '雷', '氷', '龍', '毒', '麻痺', '睡眠', '爆破' ],
    },
    expanded: true,
    weapon: {
      attack: 0,
    },
  }),

  created() {
    if (this.$store.getters.equipmentKindOf('weapon', 'id') != 0) {
      this.weapon = this.$store.state.weapon.data
    }
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'setEquipment':
            this.weapon = this.$store.state.weapon.data
            break
        }
      }
    })
  },

  methods: {
    elementName: function(value) {
      return this.labels.elements[value]
    }
  },

}
</script>