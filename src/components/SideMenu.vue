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
        :to="item.to"
        :disabled="!item.enabled"
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
      { to: '/',            icon: 'alpha-m-circle-outline', enabled: true,  label: 'メイン', },
      { to: '/weapons',     icon: 'alpha-w-circle-outline', enabled: false, label: '武器', },
      { to: '/armors',      icon: 'alpha-a-circle-outline', enabled: false, label: '防具', },
      { to: '/decorations', icon: 'alpha-d-circle-outline', enabled: true,  label: '装飾品', },
      { to: '/talismans',   icon: 'alpha-t-circle-outline', enabled: true,  label: '護石', },
      { to: '/skills',      icon: 'alpha-s-circle-outline', enabled: true,  label: 'スキル', },
      { to: '/manage',      icon: 'alpha-s-circle-outline', enabled: true,  label: '管理', },
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
