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
        <v-simple-table
          class="transparent col-12 ma-0 pa-0"
        >
          <tbody>
            <tr
              v-for="decoration in decorations.filter(item => search ? item.name === this.search: true)"
              :key="decoration.id"
            >
              <th
                :class="`text-subtitle-1 rare-${decoration.rarity}--text`"
                style="width: 11em;"
              >{{ decoration.name }}</th>
              <td
                :class="`text-center rare-${decoration.rarity}--text pa-3`"
                style="width: 4em;"
              >{{ decoration.rarity }}</td>
              <td
                class="text-center pa-3"
                style="width: 4em;"
              >{{ decoration.slot }}</td>
              <td
                class="text-left pa-3"
                style="width: 13em;"
              >{{ Object.keys(decoration.skills).join() }} <span class="text-caption text--secondary">Lv {{ Object.values(decoration.skills).join() }}</span></td>
              <td
                class="text-left pa-3"
                style="width: auto;"
                v-html="materialList(decoration.forging_materials || null)"
              ></td>
              <td
                class="text-center pa-3"
                style="width: 10em;"
              >{{ decoration.forge_funds }}</td>
            </tr>
          </tbody>
        </v-simple-table>
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
      this.loading = false
    }).then(() => {
      //console.log(this.decorations)
    })
  },

  computed: {
    //
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
