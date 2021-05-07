<template>
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

      <v-list-item
        class="d-flex flex-column align-start ma-0 px-1 py-0"
        style="min-height:auto;"
      >
        <template
        >
          <v-checkbox
            v-for="(item, i) in items"
            :key="i"
            v-model="selected"
            dense
            :value="`${item.target}+${item.value}`"
            _value="item.id"
            class="py-0"
          >
            <template v-slot:label>
              <div
                class="d-flex flex-wrap justify-space-between align-center"
              >
                <div class="text-subtitle-1" style="min-width:100px;">{{ item.name }}</div>
                <span class="text-caption grey--text" style="min-width:64px;">{{ item.dispHint }}</span>
              </div>
            </template>
          </v-checkbox>
        </template>
      </v-list-item>
    </v-list-group>
  </v-list>
</template>

<script>
export default {
  name: 'ItemConfig',

  components: {
    //
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: '所持アイテム効果',
    },
    items: [
      { id: 1, name: '力の護符', target: 'attack', value: 6, dispHint: '攻撃力+6' },
      { id: 2, name: '力の爪', target: 'attack', value: 9, dispHint: '攻撃力+9' },
      { id: 3, name: '守りの護符', target: 'defense', value: 12, dispHint: '防御力+12' },
      { id: 4, name: '守りの爪', target: 'defense', value: 18, dispHint: '防御力+18' },
    ],
    expanded: false,
    selected: [],
    attack: 0,
    defense: 0,
  }),

  watch: {
    selected: function (vals) {
      let calcAtk = 0,
          calcDef = 0
      if (vals.length) {
        vals.forEach(elm => {
          if (/^attack\+/.test(elm)) {
            calcAtk += parseInt(elm.replace('attack+', ''), 10)
          } else {
            calcDef += parseInt(elm.replace('defense+', ''), 10)
          }
        })
      }
      if (this.attack != calcAtk) {
        this.attack = calcAtk
        this.$root.$emit('update:attackByItem', this.attack)
      }
      if (this.defense != calcDef) {
        this.defense = calcDef
        this.$root.$emit('update:defenseByItem', this.defense)
      }
    }
  },

  created() {
    //
  },

  mounted() {
    //
  },

  computed: {
    //
  },

  methods: {
    //
  },

}
</script>