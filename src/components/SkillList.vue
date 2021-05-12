<template>
  <v-container>
    <h2>{{ labels.title }}</h2>
    <v-divider />
    <v-row
      no-gutters
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
        <v-col
          v-for="skill in skills"
          :key="skill.id"
          cols="12"
          md="6"
          class="px-0 px-md-2 py-2"
        >
          <v-simple-table
            class="skill-info"
          >
            <tbody>
              <tr>
                <template v-if="/^(xs|sm)$/.test($vuetify.breakpoint.name)">
                  <td
                    class="d-flex flex-column justify-space-between align-stretch pa-0"
                    style="min-width: 50%;height: max-content;"
                  >
                    <div
                      class="align-self-stretch px-3"
                      style="width: 100%; padding-top: 11px; padding-bottom: 10px;"
                    >
                      <span class="text-subtitle-1">{{ skill.name }}</span>
                    </div>
                    <div
                      class="align-self-stretch pa-3"
                      style="width: 100%;height: auto;"
                    >
                      <span class="text--secondary text-left">{{ skill.description }}</span>
                    </div>
                  </td>
                  <td
                    class="pa-0"
                    style="width: 50%;"
                  >
                    <v-simple-table
                      class="transparent skill-levels"
                    >
                      <tbody>
                        <tr
                          v-for="lv in Number(skill.max_lv)"
                          :key="lv"
                        >
                          <th
                            class="text-caption"
                            style="width: 60px;"
                          >Lv {{ lv }}</th>
                          <td
                            class="text-caption text-left py-1"
                            style="width:calc(100% - 60px);"
                          >{{ skill.status[lv] }}</td>
                        </tr>
                      </tbody>
                    </v-simple-table>
                  </td>
                </template>
                <template v-else>
                  <th class="text-subtitle-1" style="width: 11em;">{{ skill.name }}</th>
                  <td class="text--secondary text-left pa-3" style="width: calc(50% - 11em);">{{ skill.description }}</td>
                  <td
                    class="pa-0"
                    style="width: 50%;"
                  >
                    <v-simple-table
                      class="transparent skill-levels"
                    >
                      <tbody>
                        <tr
                          v-for="lv in Number(skill.max_lv)"
                          :key="lv"
                        >
                          <th
                            class="text-caption"
                            style="width: 60px;"
                          >Lv {{ lv }}</th>
                          <td
                            class="text-caption text-left"
                            style="width:calc(100% - 60px);"
                          >{{ skill.status[lv] }}</td>
                        </tr>
                      </tbody>
                    </v-simple-table>
                  </td>
                </template>
              </tr>
            </tbody>
          </v-simple-table>
        </v-col>
      </template>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'SkillList',

  components: {
    //
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: 'スキル一覧',
    },
    skills: null,
    loading: true,
  }),

  created() {
    this.getData('index.php?tbl=skills')
  },

  mounted() {
  },

  computed: {
    //
  },

  methods: {
    getData: function(path) {
      const instance = this.createAxios()
      instance.get(path)
      .then(response => {
        this.skills = response.data
        this.skills.sort((a, b) => {
            /*
            let an = a.name.toUpperCase(),
                bn = b.name.toUpperCase()
            if (an < bn) return -1
            if (an > bn) return 1
            return 0
            */
            return a.name.localeCompare(b.name, 'ja')
        })
        //console.log('SkillList.vue::getData:', response.data, this.skills)
      })
      .catch(error => {
        console.error(`Failure to retrieve skill data. (${error})`)
      })
      .finally(() => {
        this.sleep(1000).then(() => {
          this.loading = false
        }).then(() => {
          this.adjustCellHeight()
        })
      })
    },
    adjustCellHeight: function() {
      document.querySelectorAll('.skill-levels table').forEach((elm) => {
        let parentHeight = elm.closest('td').clientHeight,
            selfHeight = elm.clientHeight
        if (parentHeight > selfHeight) {
          elm.style.height = `${parentHeight}px`
        }
      })
    },
  },

}
</script>
