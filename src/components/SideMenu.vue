<template>
  <v-list
    nav
    dense
    class="mt-16"
  >
    <v-subheader>{{ labels.subheader }}</v-subheader>
    <v-list-item-group
      v-model="group"
      active-class="cyan--text text--accent-4"
    >
      <v-list-item
        v-for="(item, i) in items"
        :key="i"
        _:to="item.slug"
      >
        <v-list-item-icon>
          <v-icon
            v-text="`mdi-${item.icon}`"
            dense
          ></v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title v-text="item.label" />
        </v-list-item-content>
      </v-list-item>
    </v-list-item-group>
  </v-list>
</template>

<script>
export default {
  name: 'SideMenu',

  data: () => ({
    group: null,
    labels: {
      subheader: '機能メニュー',
    },
    items: [
      { slug: 'main',        icon: 'alpha-m-circle-outline', label: 'メイン', },
      { slug: 'weapons',     icon: 'alpha-w-circle-outline', label: '武器', },
      { slug: 'armors',      icon: 'alpha-a-circle-outline', label: '防具', },
      { slug: 'decorations', icon: 'alpha-d-circle-outline', label: '装飾品', },
      { slug: 'talismans',   icon: 'alpha-t-circle-outline', label: '護石', },
      { slug: 'skills',      icon: 'alpha-s-circle-outline', label: 'スキル', },
    ],
  }),

  watch: {
    group () {
      this.$root.$emit('update:drawer', false)
    },
  },
  
  created() {
    //
  },
}
</script>
