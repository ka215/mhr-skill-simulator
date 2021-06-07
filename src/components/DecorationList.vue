<template>
  <v-container
    fluid
  >
    <div class="d-flex justify-space-between">
      <h2 class="text-h6 amber--text">{{ labels.title }}</h2>
      <template v-if="!loading">
        <v-spacer />
        <v-autocomplete
          v-model="search"
          :items="decorations.map(elm => elm.name)"
          :label="labels.search"
          clearable
          dense
          hide-details
          :no-data-text="labels.noData"
          :style="`max-width: ${$vuetify.breakpoint.name === 'xs' ? '60%': ($vuetify.breakpoint.name === 'sm' ? '40%': '20%')};`"
          @click:clear="search = null"
        ></v-autocomplete>
      </template>
    </div>
    <v-divider />
    <v-card
      outlined
      class="my-4 pa-0"
    >
      <template v-if="loading">
        <v-col
          cols="12"
          class="d-flex justify-center align-center"
          style="height:calc(100vh - 256px);"
        >
          <v-progress-circular
            :size="32"
            :width="3"
            indeterminate
            :color="`grey ${$vuetify.theme.isDark ? 'darken-3': 'ligten-4'}`"
            style="text-center align-center"
          ></v-progress-circular>
        </v-col>
      </template>
      <template v-else>
        <v-data-table
          :headers="headers"
          :items="decorations"
          :items-per-page="-1"
          :search="search"
          hide-default-footer
          class="decoration-list elevation-0"
        >
          <template v-slot:item.name="{ item }">
            <span
              :class="`text-subtitle-1 rare-${item.rarity}--text`"
            >{{ item.name }}</span>
          </template>
          <template v-slot:item.rarity="{ item }">
            <span
              :class="`text-subtitle-1 rare-${item.rarity}--text`"
            >{{ item.rarity }}</span>
          </template>
          <template v-slot:item.skills="{ item }">
            {{ Object.keys(item.skills).join() }}
            <span
              class="text-caption text--secondary"
            >Lv {{ Object.values(item.skills).join() }}</span>
          </template>
          <template v-slot:item.forging_materials="{ item }">
            <span
              v-html="materialList(item.forging_materials || null)"
            ></span>
          </template>
        </v-data-table>
      </template>
    </v-card>
  </v-container>
</template>

<script>
export default {
  name: 'DecorationList',

  data: () => ({
    labels: {
      title: '装飾品一覧',
      search: '装飾品検索',
      noData: '該当する装飾品はありません。',
    },
    headers: [
      { value: 'name',              align: 'start',  sortable: false, width: 105, text: '装飾品名' },
      { value: 'rarity',            align: 'center', sortable: true,  width: 85,  text: 'レア度'   },
      { value: 'slot',              align: 'center', sortable: true,  width: 95,  text: 'スロット' },
      { value: 'skills',            align: 'start',  sortable: false, width: 180, text: '発動スキル' },
      { value: 'forging_materials', align: 'start',  sortable: false, width: "auto", text: '生産素材' },
      { value: 'forge_funds',       align: 'center', sortable: true,  width: 105, text: '生産費用' },
    ],
    decorations: null,
    skills: null,
    search: null,
    loading: true,
  }),

  created() {
    this.sleep(1).then(() => {
      this.decorations = this.$store.state.decorations
      this.decorations.sort((a, b) => {
        return a.ruby_name.localeCompare(b.ruby_name, 'ja')
      })
    }).then(() => {
      this.loading = false
    })
  },

  methods: {
    materialList: function(materials) {
      let retval = []
      if (!materials) {
        retval.push('<span class="text--secondary">-</span>')
      } else {
        for (let [key, value] of Object.entries(materials)) {
          retval.push(`${key}<span class="text-caption text--secondary"> × ${value}</span>`)
        }
      }
      return retval.join(', ')
    },
  },

}
</script>
