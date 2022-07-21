<template>
  <v-row>
    <template v-if="$props.id != 0">
      <v-col cols="12" class="py-0">
        {{ labels.loadoutsName }}
      </v-col>
      <v-col cols="6" class="pr-0 border-right">
        <v-simple-table
          dense
          class="preview-loadouts"
        >
          <thead>
            <tr>
              <th colspan="2">{{ labels.equipments }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in equipmentItems"
              :key="item.id"
            >
              <td class="grey--text">{{ item.type }}</td>
              <td class="text-left" v-html="item.name"></td>
            </tr>
          </tbody>
          <tfoot><tr><th colspan="2" style="height: 1px;"></th></tr></tfoot>
        </v-simple-table>
      </v-col>
      <v-col cols="6" class="pl-0">
        <v-simple-table
          dense
          class="preview-loadouts skill-list"
        >
          <thead>
            <tr>
              <th colspan="2">{{ labels.skills }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="skill in skills"
              :key="skill.id"
            >
              <td class="text-left" v-html="skill.name"></td>
              <td>
                <div
                  v-if="skill.level"
                  class="d-flex justify-start ml-5"
                >
                  <v-icon
                    v-for="n of skill.level"
                    :key="`skill-${n}`"
                    dense
                    :class="$vuetify.theme.isDark ? 'cyan--text text--darken-1 ml-n2': 'cyan--text ml-n2'"
                  >mdi-label-variant</v-icon>
                  <template v-if="skill.maxlv > skill.level">
                    <v-icon
                      v-for="n of (skill.maxlv - skill.level)"
                      :key="`skill-remain${n}`"
                      dense
                      :class="$vuetify.theme.isDark ? 'grey--text text--darken-2 ml-n2': 'grey--text ml-n2'"
                    >mdi-label-variant</v-icon>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-col>
    </template>
    <template v-else>
      <v-col cols="12" class="grey--text text-center">
        {{ labels.noSelected }}
      </v-col>
    </template>
  </v-row>
</template>

<style lang="scss">
.preview-loadouts table tbody tr:hover { background-color: transparent !important; }
.skill-list table tbody td { border-bottom: none !important; }
.border-right {
  .theme--dark &  { border-right: thin dotted rgba(#ffffff, 0.06); }
  .theme--light & { border-right: thin dotted rgba(#000000, 0.1); }
}
</style>

<script>
export default {
  name: 'LoadoutsPreview',

  props: {
    id: {
      Type: Number,
      default: 0,
    },
  },

  data: () => ({
    labels: {
      loadoutsName: null,
      noSelected: 'マイセットが選択されていません',
      equipments: '装備内容',
      skills: '発動スキル',
    },
    equipmentItems: {},
    skills: [],
  }),

  watch: {
    id: function(value) {
      if (value != 0) {
        this.createPreview(value)
      }
    },
  },

  created() {
    //console.log('LoadoutsPreview.vue::created:', this.$props.id, this.$store.getters.itemsById('loadouts', this.$props.id))
  },

  methods: {
    createPreview: function(id) {
      const kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'],
            loadouts = this.$store.getters.itemsById('loadouts', id),
            defaults = {
              weapon:   { type: '武器', name: null },
              head:     { type: '頭部', name: null },
              chest:    { type: '胸部', name: null },
              arms:     { type: '腕部', name: null },
              waist:    { type: '腰部', name: null },
              legs:     { type: '脚部', name: null },
              talisman: { type: '護石', name: null },
            }
      this.labels.loadoutsName = loadouts.name
      this.equipmentItems = defaults
      //console.log('LoadoutsPreview.vue::createPreview:', loadouts)
      kinds.forEach(kind => {
        let _tmp = null
        if (loadouts[`${kind}_id`] != 0) {
          switch (kind) {
            case 'weapon':
              _tmp = this.$store.getters.itemsById('weapons', loadouts[`${kind}_id`])
              //console.log(this.getEquipType(kind, _tmp.type), _tmp.name)
              this.equipmentItems[kind].type = this.getEquipType(kind, _tmp.type)
              this.equipmentItems[kind].name = `<span class="rare-${_tmp.rarity}--text">${_tmp.name}</span>`
              break
            case 'talisman':
              _tmp = this.$store.getters.itemsById('talismans', loadouts[`${kind}_id`])
              //console.log(this.getEquipType(kind, 0), _tmp)
              if (_tmp) {
                this.equipmentItems[kind].name = `<span class="rare-${_tmp.rarity}--text">${_tmp.name}</span>`
              } else {
                this.equipmentItems[kind].name = '<span class="muted--text">削除されました</span>'
              }
              break
            default:
              _tmp = this.$store.getters.itemsById('armors', loadouts[`${kind}_id`])
              //console.log(this.getEquipType('armor', _tmp.part), _tmp[`name_${this.$store.getters.playerDataOf('gender')}`])
              this.equipmentItems[kind].name = `<span class="rare-${_tmp.rarity}--text">${_tmp[`name_${this.$store.getters.playerDataOf('gender')}`]}</span>`
              break
          }
        }
      })
      if (Object.keys(loadouts.skills).length > 0) {
        this.skills = []
        for (let [skill, lv] of Object.entries(loadouts.skills)) {
          let _skill = this.$store.state.skills.filter(item => item.name === skill)[0]
          if (lv > _skill.max_lv) {
            lv = _skill.max_lv
            skill = `<span class="excess-level--text">${skill}</span>`
          } else
          if (lv == _skill.max_lv) {
            skill = `<span class="max-level--text">${skill}</span>`
          } else {
            skill = `<span class="below-level--text">${skill}</span>`
          }
          this.skills.push({ id: _skill.id, name: skill, level: lv, maxlv: _skill.max_lv })
        }
      }
    }
  },
}
</script>
