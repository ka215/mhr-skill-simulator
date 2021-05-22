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
        class="d-flex justify-start align-center pa-0"
      >
        <div
          :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, 'col-3', 'py-0']"
        >
          {{ labels.genders }}
        </div>
        <div class="col-9 py-0">
          <v-switch
            v-model="gender"
            flat
            dense
            :label="currentGender"
            color="indigo"
            false-value="female"
            true-value="male"
          ></v-switch>
        </div>
      </v-list-item>
      <v-divider />
      <v-list-item-title
        :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, 'pa-3']"
      >
        {{ labels.items }}
      </v-list-item-title>
      <v-list-item
        class="d-flex flex-column align-start ma-0 pa-0"
        style="min-height:auto;"
      >
        <v-checkbox
          v-for="(item, i) in items"
          :key="i"
          v-model="selected"
          dense
          :value="item"
          class="py-0 px-2"
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
      </v-list-item>
    </v-list-group>
  </v-list>
</template>

<script>
export default {
  name: 'PlayerConfig',

  data: () => ({
    gender: null,
    labels: {
      title: 'プレイヤー設定',
      genders: '性別',
      items: '所持アイテム効果',
    },
    items: [
      { id: 1, name: '力の護符', target: 'attack', value: 6, dispHint: '攻撃力+6' },
      { id: 2, name: '力の爪', target: 'attack', value: 9, dispHint: '攻撃力+9' },
      { id: 3, name: '守りの護符', target: 'defense', value: 12, dispHint: '防御力+12' },
      { id: 4, name: '守りの爪', target: 'defense', value: 18, dispHint: '防御力+18' },
    ],
    expanded: false,
    selected: [],
  }),

  watch: {
    gender: function (value) {
      this.$store.dispatch('setPlayerData', {property: 'gender', value: value})
    },
    selected: function (items) {
      this.$store.dispatch('setPlayerData', {property: 'items', value: items})
    },
  },

  created() {
    this.gender   = this.$store.getters.playerDataOf('gender')
    this.selected = this.$store.getters.playerDataOf('items')
  },

  mounted() {
    //
  },

  computed: {
    currentGender: function() {
      //console.log('PlayerConfig.vue::currentGender:', this.$store.state.player, this.$store.getters.playerDataOf('gender'))
      return this.$store.getters.playerDataOf('gender') === 'male' ? '男性': '女性'
    },
  },

  methods: {
    //
  },

}
</script>