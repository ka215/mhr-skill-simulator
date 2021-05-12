<template>
  <v-card
    outlined
    tile
    class="d-flex flex-wrap mb-2 pa-2"
  >
    <template v-if="loading">
      <div class="pa-2 text-center" style="width:100%">
        <v-progress-circular
          :size="32"
          :width="3"
          indeterminate
          :color="`grey ${$vuetify.theme.isDark ? 'darken-3': 'ligten-4'}`"
        ></v-progress-circular>
      </div>
    </template>
    <template v-else-if="item">
      <v-card-text
        class="text-subtitle-2 muted--text mx-0 py-1 px-0 col-2"
      >
        <template v-if="$props.type === 'weapon'">
          {{ item.type | itemType($props.type) }}
        </template>
        <template v-else-if="$props.type === 'armor'">
          {{ item.part | itemType($props.type) }}
        </template>
        <template v-else-if="$props.type === 'talisman'">
          {{ 0 | itemType($props.type) }}
        </template>
      </v-card-text>
      <v-card-title
        :class="['text-subtitle-1', `rare-${item.rarity}--text`, 'mx-0', 'pa-1', 'col-8']"
      >{{ item.name }}</v-card-title>
      <v-card-title
        :class="['text-caption', `rare-${item.rarity}--text`, 'text-no-wrap', 'mx-0', 'py-1', 'px-0', 'col-2']"
      >RARE {{ item.rarity }}</v-card-title>
      <v-card-actions
        class="d-flex flex-wrap justify-space-between py-1 px-0 col-12"
      >
        <div
          class="d-flex align-center"
        >
          <Talisman :slotType="item.slot1" attached="" />
          <Talisman :slotType="item.slot2" attached="" />
          <Talisman :slotType="item.slot3" attached="" />
        </div>
        <template v-if="$props.type === 'armor'">
          <div
            class="mx-1"
          >
            <v-menu>
              <template v-slot:activator="{on, attrs}">
                <v-btn
                  small
                  text
                  v-bind="attrs"
                  v-on="on"
                >Lv {{ selectedLevel || item.level }}/{{ item.max_level }}</v-btn>
              </template>
              <v-list dense>
                <v-list-item-group
                  v-model="selectedLevel"
                >
                  <v-list-item
                    v-for="n in item.max_level"
                    :key="n"
                    :value="n"
                  >
                    <v-list-item-title
                      class="text-caption"
                    >{{ 'Lv ' + n + '/' + item.max_level }}</v-list-item-title>
                  </v-list-item>
                </v-list-item-group>
              </v-list>
            </v-menu>
          </div>
        </template>
        <div class="text-right">
          <v-btn
            class="btn-tertiary mr-2"
            elevation="0"
            small
            :disabled="!hasSlot"
            @click="openDialog('SlotEditor')"
          >{{ labels.edit }}</v-btn>
          <v-btn
            class="btn-secondary"
            elevation="0"
            small
            @click="openDialog('EquipChanger')"
          >{{ labels.change }}</v-btn>
        </div>
      </v-card-actions>
    </template>
    <template v-else>
      <v-card-text
        class="text-subtitle-2 muted--text mx-0 py-1 px-0 col-2"
      ><v-icon class="muted--text">mdi-minus</v-icon></v-card-text>
      <v-card-title
        class="text-subtitle-1 muted--text mx-0 pa-1 col-8"
      ><v-icon class="muted--text">mdi-minus</v-icon></v-card-title>
      <v-card-title
        class="text-caption muted--text text-no-wrap mx-0 py-1 px-0 col-2"
      ><v-icon class="muted--text">mdi-minus</v-icon></v-card-title>
      <v-card-actions
        class="d-flex flex-wrap justify-space-between py-1 px-0 col-12"
      >
        <div
          class="d-flex align-center"
        >
          <Talisman slotType="0" attached="" />
          <Talisman slotType="0" attached="" />
          <Talisman slotType="0" attached="" />
        </div>
        <div class="text-right">
          <v-btn
            class="btn-tertiary mr-2"
            elevation="0"
            small
            disabled
          >{{ labels.edit }}</v-btn>
          <v-btn
            class="btn-secondary"
            elevation="0"
            small
            @click="openDialog('EquipChanger')"
          >{{ labels.change }}</v-btn>
        </div>
      </v-card-actions>
    </template>
  </v-card>
</template>

<script>
import Talisman from '@/components/Talisman'
//import mockData from '@/../public/mock_data.json'

export default {
  name: 'EquipmentItem',

  components: {
    Talisman,
  },

  props: {
    type: {
      Type: String,
      default: null,
    },
    id: {
      Type: Number,
      default: 0,
    }
  },

  data: () => ({
    item: null,
    hasSlot: false,
    selectedLevel: null,
    labels: {
      edit: '着脱',
      change: '変更',
    },
    loading: true,
  }),

  watch: {
    selectedLevel: function (newlv, oldlv) {
      console.log('EquipmentItem.vue::change:selectedLevel', oldlv || 1, '->', newlv, this.item.part)
      this.$root.$emit('update:defenseLevel', newlv, this.item.part)
    },
  },

  created() {
    this.getData('mock_data.json')
    /*
    let items = mockData[`${this.$props.type}s`].filter(item => item.id == Number(this.$props.id))
    if (items) {
      this.item = items.shift()
      this.hasSlot = (this.item.slot1 + this.item.slot2 + this.item.slot3) > 0
    } else {
      this.item = {
        name: '-', rarity: 0, 
      }
    }
    */
  },

  mounted() {
    //
  },

  computed: {
    //
  },

  filters: {
    itemType: (value, equipType) => {
      let equipTypeSet = {
          weapon: [
            '大剣',
            '太刀',
            '片手剣',
            '双剣',
            '鎚',// 'ハンマー',
            '狩猟笛',
            '槍',// 'ランス',
            '銃槍',// 'ガンランス',
            '剣斧',// 'スラッシュアックス',
            '盾斧',// 'チャージアックス',
            '操虫棍',
            '軽弩',// 'ライトボウガン',
            '重弩',// 'ヘビィボウガン',
            '弓'
          ],
          armor: [
            '頭部',
            '胴部',
            '腕部',
            '腰部',
            '脚部'
          ],
          talisman: [ '護石' ],
      }
      //console.log(value, equipTypeSet[equipType][value])
      return equipTypeSet[equipType][value]
    },
  },

  methods: {
    openDialog: function (target) {
      //console.log(`EquipmentItem.vue::beforeEmit.open:${target}`, this.item)
      this.$root.$emit(`open:${target}`, this.item)
    },
    getData: function(path) {
      const instance = this.createAxios()
      instance.get(path)
      .then(response => {
        let items = response.data[`${this.$props.type}s`].filter(item => item.id == Number(this.$props.id))
        if (items) {
          this.item = items.shift()
          this.hasSlot = (this.item.slot1 + this.item.slot2 + this.item.slot3) > 0
        } else {
          this.item = {
            name: '-', rarity: 0, 
          }
        }
        console.log('EquipmentItem.vue::getData:', this.item)
      })
      .catch(error => {
        console.error(`Failure to retrieve equipment data. (${error})`)
      })
      .finally(() => {
        this.sleep(500).then(() => {
          this.loading = false
        })
      })
    },
  },

}
</script>