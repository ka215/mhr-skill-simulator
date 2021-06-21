<template>
  <v-card
    outlined
  >
    <v-list>
      <v-list-group
        v-model="expanded"
        no-action
      >
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title
              class="sub-headline--text"
            >{{ labels.title }} </v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-item-content
          class="px-4"
        >
          <v-list-item-subtitle
            :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-2': !$vuetify.theme.isDark}, 'mb-2']"
          >{{ labels.subtitle }}</v-list-item-subtitle>

          <div
            v-if="talismans && talismans.length > 0"
            class="my-3 pa-2"
          >
            <v-autocomplete
              v-model="search"
              :items="ac_skills !== null ? ac_skills: []"
              :label="labels.search"
              clearable
              dense
              :no-data-text="labels.noSkillData"
            ></v-autocomplete>
          </div>

          <v-data-table
            v-model="selected"
            :headers="headers"
            :items="talismans != null ? talismans: []"
            item-key="id"
            single-select
            dense
            :no-results-text="labels.noTalismanResults"
            :no-data-text="labels.noTalismanData"
            :footer-props="{'items-per-page-text': labels.rowPerPage, 'items-per-page-all-text': labels.perPageAll, 'page-text': labels.pageText}"
            :search="search"
            click:row="deleteOne(item.id)"
            class="talisman-info"
          >
            <template v-slot:item.name="{ item }">
              <div :class="`rare-${item.rarity}--text`">{{ item.name }}</div>
            </template>
            <template v-slot:item.rarity="{ item }">
              <div :class="`rare-${item.rarity}--text`">
                {{ /^(xs|sm)$/.test($vuetify.breakpoint.name) ? item.name : item.rarity }}
              </div>
            </template>
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
                  class="d-flex justify-start align-center mr-2"
                >
                  <span class="text-caption">{{ skill }}</span>
                  <v-icon small class="font-weight-thin text--secondary ml-1">mdi-numeric-{{ lv }}-circle-outline</v-icon>
                </div>
              </div>
            </template>
            <template v-slot:item.worth="{ item }">
              <div
                class="d-flex justify-space-between align-center ma-0"
              >
                <template v-if="!/^(xs|sm)$/.test($vuetify.breakpoint.name)">
                  <div class="text-center" style="width: 60px;">{{ item.worth.toString() }}</div>
                  <v-divider vertical class="border-dashed" />
                </template>
                <StarRating :evaluation="Math.ceil(item.worth / 20)" />
              </div>
            </template>
            <template v-slot:item.id="{ item }">
              <v-btn
                class="btn-default"
                small
                depressed
                elevation="0"
                @click="deleteOne(item.id)"
              >{{ labels.delete }}</v-btn>
            </template>
          </v-data-table>
        </v-list-item-content>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            class="btn-default"
            :disabled="!talismans || talismans.length == 0"
            @click="deleteAll()"
          >{{ labels.deleteAll }}</v-btn>
          <v-btn
            class="btn-primary"
            :disabled="false"
            @click="importData()"
          >{{ labels.import }}</v-btn>
          <v-btn
            class="btn-primary"
            :disabled="!talismans || talismans.length == 0"
            @click="exportData()"
          >{{ labels.export }}</v-btn>
        </v-card-actions>
      </v-list-group>
    </v-list>
  </v-card>
</template>

<script>
import Talisman from '@/components/Talisman'
import StarRating from '@/components/StarRating'

export default {
  name: 'TalismanManager',

  components: {
    Talisman,
    StarRating,
  },

  data: () => ({
    expanded: false,
    labels: {
      title: '護石管理',// 'Talisman Management'
      subtitle: '登録済みの護石の確認・削除を行うことができます。',
      search: 'スキル検索',
      noTalismanResults: '該当する護石がありません。',
      noTalismanData: "護石データがありません。護石を登録してください。",
      rowPerPage: '表示数:',
      perPageAll: 'すべて',
      pageText: '{0}-{1}件/{2}件',
      delete: '削除',
      deleteAll: '全ての護石削除',
      import: 'インポート',
      export: 'エクスポート',
      confirmation: '確認',
      information: '通知',
      error: 'エラー',
    },
    talismans: null,
    search: null,
    ac_skills: null,
    selected: [],
    headers: [/* breakpoint >= md */
      { align: 'start',  value: 'name',          width: 105,   sortable: false, filterable: false, text: '護石名', },
      { align: 'center', value: 'rarity',        width: 85,    filterable: false, text: 'レア度', },
      { align: 'center', value: 'slots',         width: 105,   filterable: false, text: 'スロット', },
      { align: 'start',  value: 'skills_text',   width: 'calc(100% - 660px)', sortable: false, text: 'スキル', },
      { align: 'center', value: 'worth',         width: 280,   filterable: true, text: '護石評価', },/*
      { align: 'center', value: 'emission_type', width: 105,   filterable: false, text: '排出タイプ', },
      { align: 'center', value: 'emissions',     width: 105,   filterable: false, text: '排出数', },*/
      { align: 'center', value: 'id',            width: 85,    sortable: false, filterable: false, text: '管理', },
    ],
    resultNotes: null,
  }),

  watch: {
    resultNotes: function(value) {
      this.sleep(300).then(() => {
        this.reloadTalismans()
        if (value) {
          this.$root.$emit('open:notification', value)
        }
      })
    },
  },

  created() {
    switch(this.$vuetify.breakpoint.name) {
      case 'xs':
        this.headers = [
          { align: 'start', value: 'rarity',      filterable: false, text: '護石名', },
          { align: 'start', value: 'slots',       filterable: false, text: 'スロット', },
          { align: 'start', value: 'skills_text', sortable: false,   text: 'スキル', },
          { align: 'start', value: 'worth',       filterable: true,  text: '護石評価', },
          { align: 'start', value: 'id',          sortable: false, filterable: false, text: '管理', },
        ]
        break
      case 'sm':
        this.headers = [
          { align: 'start',  value: 'rarity',      width: 105,   filterable: false, text: '護石名', },
          { align: 'center', value: 'slots',       width: 105,   filterable: false, text: 'スロット', },
          { align: 'start',  value: 'skills_text', width: 'calc(100% - 460px)', sortable: false, text: 'スキル', },
          { align: 'center', value: 'worth',       width: 165,   filterable: true, text: '護石評価', },
          { align: 'center', value: 'id',          width: 85,    sortable: false, filterable: false, text: '管理', },
        ]
        break
    }
    this.loadTalismans()
    this.$root.$on('delete:talisman', (params) => {
      let equippedTalismanId = this.$store.getters.equipmentKindOf('talisman', 'id')
      if (equippedTalismanId) {
        let deleteTalismanId = params.conditions[0][2]
        //console.log('ここで装備中の護石を取り外す。装備中の護石:', equippedTalismanId, deleteTalismanId )
        if (Array.isArray(deleteTalismanId) && deleteTalismanId.includes(equippedTalismanId)) {
          this.$store.dispatch('setEquipment', {property: 'talisman', data: {}, slots: {}})
          //console.log('take off: %d', equippedTalismanId)
        } else
        if (deleteTalismanId == equippedTalismanId) {
          this.$store.dispatch('setEquipment', {property: 'talisman', data: {}, slots: {}})
          //console.log('take off: %d', equippedTalismanId)
        }
      }
      this.saveData('put', params, (response) => {
        let notices = {
          title: this.labels.information,
          messages: [],
          close: '閉じる',
          emit: null,
        }
        if (response.data.state == 201) {
          notices.messages = [ '護石を削除しました。' ]
          let deleteTalismanId = params.conditions[0][2],
              baseRequest = 'index.php?tbl=talismans&filters[id]='
          if (Array.isArray(deleteTalismanId)) {
            deleteTalismanId.forEach(id => {
              this.$userCache.remove(baseRequest + id, null, (result) => {
                if (result) {
                  this.$store.dispatch('removeItemById', {property: 'talismans', data: id})
                  //console.log(`護石ID:${id}がキャッシュから削除された。`)
                }
              })
            })
            this.resultNotes = notices
          } else {
            this.$userCache.remove(baseRequest + deleteTalismanId, null, (result) => {
              if (result) {
                this.$store.dispatch('removeItemById', {property: 'talismans', data: deleteTalismanId})
                //console.log(`護石ID:${deleteTalismanId}がキャッシュから削除された。`)
              }
            }).finally(() => {
              this.resultNotes = notices
            })
          }
        } else {
          notices.title = this.labels.error
          notices.messages = [ '護石の削除に失敗しました。', 'もう一度お試しください。' ]
          this.resultNotes = notices
        }
      })
    })
    this.$root.$on('import:talismans', () => {
      this.import('talismans')
    })
    this.$root.$on('export:talismans', () => {
      this.export('talismans')
    })
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'addData':
          case 'initData':
            this.loadTalismans()
            break
        }
      }
    })
  },

  methods: {
    loadTalismans: function() {
      const allTalismans = this.$store.state.talismans
      //console.log('TalismanManager.vue::loadTalismans:', this.$store.state.talismans)
      let has_skills = []
      allTalismans.forEach((item, i, self) => {
        item.slots = Number(`${item.slot1}${item.slot2}${item.slot3}`)
        let tmp_array  = []
        for (let [key, value] of Object.entries(item.skills)) {
          tmp_array.push(`${key}(${value})`)
          if (!has_skills.includes(key)) {
            has_skills.push(key)
          }
        }
        item.skills_text = tmp_array.join(', ')
        self[i] = item
      })
      allTalismans.sort((a, b) => {
        return (a.rarity < b.rarity ? 1: -1)
      })
      allTalismans.sort((a, b) => {
        return (a.worth < b.worth ? 1: -1)
      })
      this.ac_skills = has_skills
      this.talismans = allTalismans
    },
    deleteOne: function(id) {
      const params = {
        table: 'talismans',
        data: {disabled: true},
        conditions: [ ['id', '=', id], ['disabled', '=', false] ],
        operator: 'and',
      }
      let notices = {
        title: this.labels.confirmation,
        messages: [ 'この護石を削除してよろしいですか？' ],
        close: 'キャンセル',
        commit: '削除する',
        emit: 'delete:talisman',
        data: params,
      }
      if (id == this.$store.getters.equipmentKindOf('talisman', 'id')) {
        notices.messages = [ '<span class="red--text">現在装備中の護石です。</span>', '削除すると装備から外されますがよろしいですか？' ]
      }
      this.$root.$emit('open:notification', notices)
    },
    deleteAll: function() {
      const deleteIds = this.talismans.map(item => item.id)
      const params = {
        table: 'talismans',
        data: {disabled: true},
        conditions: [ ['id', 'in', deleteIds], ['disabled', '=', false] ],
        operator: 'and',
      }
      let notices = {
        title: this.labels.confirmation,
        messages: [ '<span class="red--text">すべての護石を削除します。</span>', '装備中の護石は外されます。削除してよろしいですか？' ],
        close: 'キャンセル',
        commit: '削除する',
        emit: 'delete:talisman',
        data: params,
      }
      this.$root.$emit('open:notification', notices)
    },
    reloadTalismans: function() {
      this.$userCache.find('talismans', cacheData => {
        this.$store.dispatch('initData', {property: 'talismans', data: cacheData})
      })
    },
    importData: function() {
      let options = {
        type: 'talismans',
        title: '護石データのインポート',
        content: [ '護石データをインポートします。', 'アップロードするJSONファイルを選んでください。' ],
        close: 'キャンセル',
        commit: 'インポートする',
      }
      this.$root.$emit('open:importer', options)
    },
    exportData: function() {
      let notices = {
        title: '護石データのエクスポート',
        messages: [ '現在の護石データをJSON形式でダウンロードします。', 'エクスポートを実行しますか？' ],
        close: 'キャンセル',
        commit: 'エクスポートする',
        emit: 'export:talismans',
      }
      this.$root.$emit('open:notification', notices)
    },
  },

}
</script>
