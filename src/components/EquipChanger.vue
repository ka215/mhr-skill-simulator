<template>
  <v-row
    justify="center"
    class="ma-0 pa-0"
  >
    <v-dialog
      v-model="dialog"
      :max-width="dialogWidth"
      overlay-color="grey darken-3"
      overlay-opacity="0.75"
    >
      <v-card
        v-if="item"
      >
        <v-card-title
          class="align-center"
        >
          {{ labels.title }}
          <v-icon dense class="grey--text">mdi-rhombus-medium</v-icon>
          <template v-if="labels.name">
            <span :class="`rare-${item.rarity}--text`">{{ labels.name }}</span>
          </template>
          <template v-else>
            <span class="muted--text">{{ labels.none }}</span>
          </template>
          <v-icon small class="grey--text text--darken-1 mx-2">mdi-arrow-right</v-icon>
          <span ref="newItem"></span>
        </v-card-title>
        <v-divider />
        <template
          v-if="kind === 'weapon'"
        >
          <v-tabs
            v-model="weaponType"
            align-with-title
            center-active
          >
            <v-tabs-slider></v-tabs-slider>
            <v-tab
              v-for="(tabItem, i) in tabs"
              :key="i"
            >
              {{ tabItem }}
            </v-tab>
          </v-tabs>
          <v-tabs-items
            v-model="weaponType"
            class="pt-4 pb-2 px-1"
          >
            <v-card
              tile
              flat
            >
              <v-data-table
                v-model="selected"
                :headers="headers.weapon"
                :items="weapons.filter((weapon) => weapon.type == weaponType)"
                item-key="id"
                single-select
                show-select
                dense
                :footer-props="{'items-per-page-text': labels.rowPerPage, 'items-per-page-all-text': labels.perPageAll, 'page-text': labels.pageText}"
                @click:row="selectedRow"
              >
                <template v-slot:item.affinity="{ item }">
                  {{ item.affinity }}<v-icon x-small class="text--secondary">mdi-percent-outline</v-icon>
                </template>
                <template v-slot:item.slots="{ item }">
                  <div style="margin-top: 3px; marign-bottom: 0;">
                    <Talisman :slotType="item.slot1" size="24" /><Talisman :slotType="item.slot2" size="24" /><Talisman :slotType="item.slot3" size="24" />
                  </div>
                </template>
              </v-data-table>
            </v-card>
          </v-tabs-items>
        </template>
        <template
          v-else-if="kind === 'armor'"
        >
          <v-card
            tile
            flat
          >
            <v-card-title class="px-5">
              <v-autocomplete
                v-model="search"
                :items="ac_skills"
                :label="labels.search"
                clearable
                dense
                :no-data-text="labels.noSkillData"
              ></v-autocomplete>
            </v-card-title>
            <v-data-table
              v-model="selected"
              :headers="headers.armor"
              :items="armors.filter((armor) => armor.part == armorPart)"
              item-key="id"
              single-select
              show-select
              dense
              :no-results-text="labels.noArmorResults"
              :no-data-text="labels.noArmorData"
              :footer-props="{'items-per-page-text': labels.rowPerPage, 'items-per-page-all-text': labels.perPageAll, 'page-text': labels.pageText}"
              :search="search"
              @click:row="selectedRow"
            >
              <template v-slot:item.slots="{ item }">
                <div style="margin-top: 3px; marign-bottom: 0;">
                  <Talisman :slotType="item.slot1" size="24" /><Talisman :slotType="item.slot2" size="24" /><Talisman :slotType="item.slot3" size="24" />
                </div>
              </template>
              <template v-slot:item.skills_text="{ item }">
                <div
                  class="d-flex flex-wrap"
                >
                  <div
                    v-for="(lv, skill) in item.skills"
                    :key="skill + lv"
                    class="mr-2"
                  >
                    <span class="text-caption">{{ skill }}</span>
                    <v-icon small class="font-weight-thin text--secondary ml-1">mdi-numeric-{{ lv }}-circle-outline</v-icon>
                  </div>
                </div>
              </template>
            </v-data-table>
          </v-card>
        </template>
        <template
          v-else-if="kind === 'talisman'"
        >
          <v-card
            tile
            flat
          >
            <v-card-title class="px-5">
              <v-autocomplete
                v-model="search"
                :items="ac_skills"
                :label="labels.search"
                clearable
                dense
                :no-data-text="labels.noSkillData"
              ></v-autocomplete>
            </v-card-title>
            <v-data-table
              v-model="selected"
              :headers="headers.talisman"
              :items="talismans"
              item-key="id"
              single-select
              show-select
              dense
              :no-results-text="labels.noTalismanResults"
              :no-data-text="labels.noTalismanData"
              :footer-props="{'items-per-page-text': labels.rowPerPage, 'items-per-page-all-text': labels.perPageAll, 'page-text': labels.pageText}"
              :search="search"
              @click:row="selectedRow"
            >
              <template v-slot:item.slots="{ item }">
                <div style="margin-top: 3px; marign-bottom: 0;">
                  <Talisman :slotType="item.slot1" size="24" /><Talisman :slotType="item.slot2" size="24" /><Talisman :slotType="item.slot3" size="24" />
                </div>
              </template>
              <template v-slot:item.skills_text="{ item }">
                <div
                  class="d-flex flex-wrap"
                >
                  <div
                    v-for="(lv, skill) in item.skills"
                    :key="skill + lv"
                    class="mr-2"
                  >
                    <span class="text-caption">{{ skill }}</span>
                    <v-icon small class="font-weight-thin text--secondary ml-1">mdi-numeric-{{ lv }}-circle-outline</v-icon>
                  </div>
                </div>
              </template>
              <!-- /* template v-slot:item.worth="{ item }">
                {{ starRating(item) }}
              </template */ -->
            </v-data-table>
          </v-card>
        </template>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            @click="dialog = false"
          >{{ labels.close }}</v-btn>
          <v-btn
            text
            :disabled="selected.length == 0"
            @click="commitEdit"
          >{{ labels.commit }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import Talisman from '@/components/Talisman'

export default {
  name: 'EquipChanger',

  components: {
    Talisman,
  },

  props: {
    //
  },

  data: () => ({
    dialog: false,
    labels: {
      title: '装備変更',
      kind: null,
      name: '',
      none: '未装備',
      search: 'スキル検索',
      noSkillData: '該当するスキルはありません。',
      noArmorResults: '該当する防具がありません。',
      noArmorData: '防具データがありません。',
      noTalismanResults: '該当する護石がありません。',
      noTalismanData: "護石データがありません。\n護石管理から登録してください。",
      rowPerPage: '表示数:',
      perPageAll: 'すべて',
      pageText: '{0}-{1}件/{2}件',
      close: '閉じる',
      commit: '決定',
    },
    item: null,// current equipment item
    weapons: null,// selectable equipment weapons
    armors: null,// selectable equipment armors
    talismans: null,// selectable equipment talismans
    ac_skills: null,
    tabs: [],
    weaponType: null,
    armorPart: null,
    tab: null,
    headers: {
      weapon: [
        { text: '武器名', align: 'start', value: 'name' },
        { text: '派生', value: 'tree' },
        { text: 'レア度', align: 'center', value: 'rarity' },
        { text: '攻撃力', align: 'center', value: 'attack' },
        { text: '会心率', align: 'center', value: 'affinity' },
        { text: '防御ボーナス', align: 'center', value: 'defense_bonus' },
        { text: '属性', align: 'center', value: 'elements' },
        { text: 'スロット', value: 'slots', width: 105 },
      ],
      armor: [
        { text: '防具名', align: 'start', value: 'name', width: 150, filterable: false },
        //{ text: 'シリーズ名', value: 'series', filterable: false },
        { text: 'レア度', align: 'center', value: 'rarity', filterable: false },
        { text: '防御力', align: 'center', value: 'defense', filterable: false },
        { text: '火耐性', align: 'center', value: 'fire_resistance', filterable: false },
        { text: '水耐性', align: 'center', value: 'water_resistance', filterable: false },
        { text: '雷耐性', align: 'center', value: 'thunder_resistance', filterable: false },
        { text: '氷耐性', align: 'center', value: 'ice_resistance', filterable: false },
        { text: '龍耐性', align: 'center', value: 'dragon_resistance', filterable: false },
        { text: 'スロット', align: 'center', value: 'slots', width: 105, filterable: false },
        { text: 'スキル', value: 'skills_text', sortable: false }
      ],
      talisman: [
        { text: '護石名', align: 'start', value: 'name', width: 120, filterable: false },
        { text: 'レア度', align: 'center', value: 'rarity', width: 90, filterable: false },
        { text: 'スロット', align: 'center', value: 'slots', width: 105, filterable: false },
        { text: 'スキル', value: 'skills_text', width: 'calc(100% - 630px)', sortable: false },
        { text: '評価', align: 'center', value: 'worth', width: 105, filterable: false },
        { text: '排出タイプ', align: 'center', value: 'emission_type', width: 105, filterable: false },
        { text: '排出数', align: 'center', value: 'emissions', width: 105, filterable: false },
      ],
    },
    search: '',
    selected: [],
  }),

  watch: {
    dialog: function (cur, old) {
      if (!cur && old) {
        //this.tab = null
        this.search = ''
        this.selected = []
      }
    },
    selected: function (value) {
      if (value.length > 0 && this.item.name !== value[0].name) {
        //console.log(value[0].name, this.item.name, this.$refs.newItem)
        this.$refs.newItem.setAttribute('class', `rare-${value[0].rarity}--text`)
        this.$refs.newItem.textContent = value[0].name
      } else {
        this.$refs.newItem.removeAttribute('class')
        this.$refs.newItem.textContent = ''
      }
    },
  },

  created() {
    //
  },

  mounted() {
    this.$root.$on('open:EquipChanger', (value) => {
      this.item = value
      this.kind = Object.prototype.hasOwnProperty.call(this.item, 'type') ? 'weapon': (Object.prototype.hasOwnProperty.call(this.item, 'part') ? 'armor': 'talisman')
      //console.log('EquipChanger.vue::open:EquipChanger', this.item, this.kind)
      this.ac_skills = this.$store.state.skills.map(item => item.name)
      switch (this.kind) {
        case 'weapon':
          this.labels.title = '武器変更'
          this.labels.name = this.item.name
          this.tabs = this.getEquipType(this.kind)
          this.weaponType = this.item.type
          this.retrieveData('weapons', () => {
            this.weapons = this.$store.state.weapons.concat()
            //console.log(this.weapons)
            this.weapons.forEach((item, i, self) => {
              let elms = [ this.getElementName(item.element1) ]
              if (item.element2 != 0) {
                elms.push(this.getElementName(item.element2))
              }
              item.elements = elms.join(', ')
              item.slots = Number(`${item.slot1}${item.slot2}${item.slot3}`)
              self[i] = item
            })
            this.weapons.sort((a, b) => {
              return a.ruby_name.localeCompare(b.ruby_name, 'ja')
            })
            this.dialog = this.weapons.length > 0
          })
          break
        case 'armor':
          this.labels.title = this.getEquipType(this.kind, this.item.part) + '防具変更'
          this.labels.name = this.item.name
          this.armorPart = this.item.part
          this.retrieveData('armors', () => {
            this.armors = this.$store.state.armors.concat()
            //console.log(this.armors)
            this.armors.forEach((item, i, self) => {
              item.name  = item[`name_${this.$store.getters.playerDataOf('gender')}`]
              item.slots = Number(`${item.slot1}${item.slot2}${item.slot3}`)
              let tmp_array = []
              for (let [key, value] of Object.entries(item.skills)) {
                tmp_array.push(`${key}(${value})`)
              }
              item.skills_text = tmp_array.join(', ')
              self[i] = item
            })
            this.armors.sort((a, b) => {
              return a[`ruby_name_${this.$store.getters.playerDataOf('gender')}`].localeCompare(b[`ruby_name_${this.$store.getters.playerDataOf('gender')}`], 'ja')
            })
            this.dialog = this.armors.length > 0
          })
          break
        case 'talisman':
          this.labels.title = this.getEquipType(this.kind, 0) + '変更'
          this.labels.name = this.item.name
          this.retrieveData('talismans', () => {
            this.talismans = this.$store.state.talismans.concat()
            //console.log(this.talismans)
            this.talismans.forEach((item, i, self) => {
              item.slots = Number(`${item.slot1}${item.slot2}${item.slot3}`)
              let tmp_array = []
              for (let [key, value] of Object.entries(item.skills)) {
                tmp_array.push(`${key}(${value})`)
              }
              item.skills_text = tmp_array.join(', ')
              self[i] = item
            })
            this.talismans.sort((a, b) => {
              return (a.rarity < b.rarity ? 1: -1)
            })
            this.talismans.sort((a, b) => {
              return (a.worth < b.worth ? 1: -1)
            })
            this.dialog = true// this.talismans.length > 0
          })
          break
      }
    })
  },

  computed: {
    dialogWidth () {
      // console.log('dialogWidth', this.$vuetify.breakpoint.name)
      switch (this.$vuetify.breakpoint.name) {
        case 'xs': return `${this.$vuetify.breakpoint.width - 30}px`
        case 'sm': return `${this.$vuetify.breakpoint.width - 80}px`
        default: return `${this.$vuetify.breakpoint.width - 200}px`
      }
    },
  },

  methods: {
    commitEdit: function() {
      if (this.selected.length == 0) {
        this.dialog = false
        return
      }
      let newItems = this[`${this.kind}s`].filter(item => item.id == this.selected[0].id)
      if (newItems.length > 0) {
        if ('armor' === this.kind) {
          let part = ['head', 'chest', 'arms', 'waist', 'legs'][newItems[0].part]
          this.$store.dispatch('setEquipment', {property: part, data: newItems[0], level: 1, slots: []})
          //console.log('EquipChanger.vue::commitEdit:saved', part, this.$store.state[part])
        } else {
          this.$store.dispatch('setEquipment', {property: this.kind, data: newItems[0], slots: []})
          //console.log('EquipChanger.vue::commitEdit:saved', this.kind, this.$store.state[this.kind])
        }
      } else {
        console.error('Failed to change equipment.')
      }
      this.dialog = false
    },
    selectedRow: function(item, self) {
      if (this.selected.length == 0) {
        this.selected.push(item)
        self.isSelected = true
      } else
      if (this.selected[0].id == item.id) {
        this.selected = []
        self.isSelected = false
      } else {
        this.selected = []
        this.selected.push(item)
        self.isSelected = true
      }
    },
    starRating: function(val) {
      console.log(val)
      return val.worth
    }
  },

}
</script>