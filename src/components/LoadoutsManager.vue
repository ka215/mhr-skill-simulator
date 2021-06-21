<template>
  <v-row>
    <v-col
      class="col-12 col-sm-6"
    >
      <v-card outlined>
        <v-list>
          <v-list-item-content
            class="px-4"
          >
            <v-list-item-title
              class="sub-headline--text mb-3"
            >{{ labels.title }} </v-list-item-title>
            <v-list-item-subtitle
              :class="['grey--text', {'text--lighten-1': $vuetify.theme.isDark}, {'text--darken-2': !$vuetify.theme.isDark}, 'mb-2']"
            >{{ labels.subtitle }}</v-list-item-subtitle>
          </v-list-item-content>

          <v-list-item-content
            class="px-4"
          >
            <div
              v-if="items && items.length > 0"
              class="mb-3 pa-2"
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
              :items="items != null ? items: []"
              item-key="id"
              single-select
              dense
              :no-results-text="labels.noLoadoutsResults"
              :no-data-text="labels.noLoadoutsData"
              :footer-props="{'items-per-page-text': labels.rowPerPage, 'items-per-page-all-text': labels.perPageAll, 'page-text': labels.pageText}"
              :search="search"
              @click:row="preview"
              class="loadouts-info"
            >
              <template v-slot:item.has_skills="{ item }">
                <span class="d-none">{{ item.has_skills }}</span>
              </template>
              <template v-slot:item.del="{ item }">
                <v-btn
                  class="btn-default"
                  small
                  depressed
                  elevation="0"
                  @click="deleteOne(item.id)"
                >{{ labels.delete }}</v-btn>
              </template>
              <template v-slot:item.equip="{ item }">
                <v-btn
                  class="btn-primary"
                  small
                  depressed
                  elevation="0"
                  @click="equip(item.id)"
                >{{ labels.equip }}</v-btn>
              </template>
            </v-data-table>
          </v-list-item-content>
          <v-divider />
          <v-card-actions>
            <v-spacer />
            <v-btn
              class="btn-default"
              :disabled="!items || items.length == 0"
              @click="deleteAll()"
            >{{ labels.deleteAll }}</v-btn>
            <v-btn
              class="btn-primary"
              :disabled="false"
              @click="importData()"
            >{{ labels.import }}</v-btn>
            <v-btn
              class="btn-primary"
              :disabled="!items || items.length == 0"
              @click="exportData()"
            >{{ labels.export }}</v-btn>
          </v-card-actions>
        </v-list>
      </v-card>
    </v-col>
    <v-col
      class="col-12 col-sm-6"
    >
      <v-card outlined>
        <v-list>
          <v-list-item-content
            class="px-4"
          >
            <v-list-item-title
              class="sub-headline--text mb-3"
            >{{ labels.preview }} </v-list-item-title>
          </v-list-item-content>
          <v-list-item-content
            class="px-4"
          >
            <LoadoutsPreview :id="previewId" />
          </v-list-item-content>
        </v-list>
      </v-card>
    </v-col>
    <template>
      <Notification />
      <Importer />
    </template>
  </v-row>
</template>

<style lang="scss">
.loadouts-info table thead tr th:first-child,
.loadouts-info table tbody tr td:first-child {
  border-right: none !important;
}
</style>

<script>
import LoadoutsPreview from '@/components/LoadoutsPreview'
import Notification from '@/components/Notification'
import Importer from '@/components/Importer'

export default {
  name: 'LoadoutsManager',

  components: {
    LoadoutsPreview,
    Notification,
    Importer,
  },

  data: () => ({
    expanded: true,
    labels: {
      title: 'マイセット管理',
      preview: 'マイセット・プレビュー',
      subtitle: '登録済みのマイセットの装備／削除を行うことができます。',
      search: 'スキル検索',
      noLoadoutsResults: '該当するマイセットがありません。',
      noLoadoutsData: "マイセットが登録されていません。",
      rowPerPage: '表示数:',
      perPageAll: 'すべて',
      pageText: '{0}-{1}件/{2}件',
      equip: '装備',
      delete: '削除',
      deleteAll: '全てのマイセット削除',
      import: 'インポート',
      export: 'エクスポート',
      confirmation: '確認',
      information: '通知',
      error: 'エラー',
    },
    headers: [
      { align: 'start',  value: 'name',                                filterable: false, text: 'マイセット名', },
      { align: 'end',    value: 'has_skills', width: 0, sortable: false,                    text: '', },
      { align: 'center', value: 'del',    width: 85,  sortable: false, filterable: false, text: '削除', },
      { align: 'center', value: 'equip',  width: 85,  sortable: false, filterable: false, text: '装備', },
    ],
    items: null,
    previewId: 0,
    search: null,
    ac_skills: null,
    selected: [],
    resultNotes: null,
  }),

  watch: {
    resultNotes: function(value) {
      this.sleep(300).then(() => {
        this.reloadLoadouts()
        if (value) {
          this.$root.$emit('open:notification', value)
        }
      })
    },
  },

  created() {
    this.loadLoadouts()
    this.$root.$on('delete:loadouts', (params) => {
      this.saveData('put', params, (response) => {
        let notices = {
          title: this.labels.information,
          messages: [],
          close: '閉じる',
          emit: null,
        }
        if (response.data.state == 201) {
          notices.messages = [ 'マイセットを削除しました。' ]
          let deleteLoadoutsId = params.conditions[0][2],
              baseRequest = 'index.php?tbl=loadouts&filters[id]='
          if (Array.isArray(deleteLoadoutsId)) {
            deleteLoadoutsId.forEach(id => {
              this.$userCache.remove(baseRequest + id, null, (result) => {
                if (result) {
                  this.$store.dispatch('removeItemById', {property: 'loadouts', data: id})
                  //console.log(`マイセットID:${id}がキャッシュから削除された。`)
                }
              })
            })
            this.resultNotes = notices
          } else {
            this.$userCache.remove(baseRequest + deleteLoadoutsId, null, (result) => {
              if (result) {
                this.$store.dispatch('removeItemById', {property: 'talismans', data: deleteLoadoutsId})
                //console.log(`マイセットID:${deleteLoadoutsId}がキャッシュから削除された。`)
              }
            }).finally(() => {
              this.resultNotes = notices
            })
          }
        } else {
          notices.title = this.labels.error
          notices.messages = [ 'マイセットの削除に失敗しました。', 'もう一度お試しください。' ]
          this.resultNotes = notices
        }
      })
    })
    this.$root.$on('import:loadouts', () => {
      this.import('loadouts')
    })
    this.$root.$on('export:loadouts', () => {
      this.export('loadouts')
    })
  },

  mounted() {
    this.$store.subscribeAction({
      after: (action) => {
        switch (action.type) {
          case 'addData':
          case 'initData':
            this.loadLoadouts()
            break
        }
      }
    })
  },

  methods: {
    loadLoadouts: function() {
      let storedLoadouts = this.$store.state.loadouts,
          _tmp = []
      storedLoadouts.forEach((elm, idx) => {
        _tmp = _tmp.concat(Object.keys(elm.skills))
        storedLoadouts[idx].has_skills = Object.keys(elm.skills).join(',')
      })
      this.ac_skills = this.arrayUnique(_tmp)
      this.items = storedLoadouts
    },
    preview: function(item) {
      //const current_loadouts = this.$store.getters.itemsById('loadouts', item.id)
      this.previewId = item.id
      //console.log('preview:', item, this.previewId)
    },
    equip: function(id) {
      const kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'],
            new_loadouts = this.$store.getters.itemsById('loadouts', id)
      this.$store.dispatch('removeEquipment', {kind: 'all'})
      kinds.forEach(kind => {
        let _slots = {}
        switch (kind) {
          case 'weapon':
          case 'talisman':
            if (new_loadouts[`${kind}_id`] && new_loadouts[`${kind}_id`] != 0) {
              _slots = new_loadouts[`${kind}_slots`]
              //console.log(kind, new_loadouts[`${kind}_id`], Object.keys(_slots).length)
              this.$store.dispatch('setEquipment', {
                property: kind,
                data: this.$store.getters.itemsById(`${kind}s`, new_loadouts[`${kind}_id`]),
                slots: Object.keys(_slots).length > 0 ? _slots: {}
              })
            }
            break
          default:
            if (new_loadouts[`${kind}_id`] && new_loadouts[`${kind}_id`] != 0) {
              _slots = new_loadouts[`${kind}_slots`]
              //console.log(kind, new_loadouts[`${kind}_id`], Object.keys(_slots).length)
              this.$store.dispatch('setEquipment', {
                property: kind,
                data: this.$store.getters.itemsById(`armors`, new_loadouts[`${kind}_id`]),
                level: new_loadouts[`${kind}_lv`],
                slots: Object.keys(_slots).length > 0 ? _slots: {}
              })
            }
            break
        }
      })
      this.$store.dispatch('initData', {property: 'now_loadouts', data: id})
      this.$router.push('/')
    },
    deleteOne: function(id) {
      const params = {
        table: 'loadouts',
        data: {disabled: true},
        conditions: [ ['id', '=', id], ['disabled', '=', false] ],
        operator: 'and',
      }
      let notices = {
        title: this.labels.confirmation,
        messages: [ 'このマイセットを削除してよろしいですか？' ],
        close: 'キャンセル',
        commit: '削除する',
        emit: 'delete:loadouts',
        data: params,
      }
      this.$root.$emit('open:notification', notices)
    },
    deleteAll: function() {
      const deleteIds = this.items.map(item => item.id)
      const params = {
        table: 'loadouts',
        data: {disabled: true},
        conditions: [ ['id', 'in', deleteIds], ['disabled', '=', false] ],
        operator: 'and',
      }
      let notices = {
        title: this.labels.confirmation,
        messages: [ '<span class="red--text">すべてのマイセットを削除します。</span>', '削除してよろしいですか？' ],
        close: 'キャンセル',
        commit: '削除する',
        emit: 'delete:loadouts',
        data: params,
      }
      this.$root.$emit('open:notification', notices)
    },
    reloadLoadouts: function() {
      this.$userCache.find('loadouts', cacheData => {
        this.$store.dispatch('initData', {property: 'loadouts', data: cacheData})
      })
    },
    importData: function() {
      let options = {
        type: 'loadouts',
        title: 'マイセットデータのインポート',
        content: [ 'マイセットデータをインポートします。', 'アップロードするJSONファイルを選んでください。' ],
        close: 'キャンセル',
        commit: 'インポートする',
      }
      this.$root.$emit('open:importer', options)
    },
    exportData: function() {
      let notices = {
        title: 'マイセットデータのエクスポート',
        messages: [ '現在のマイセットデータをJSON形式でダウンロードします。', 'エクスポートを実行しますか？' ],
        close: 'キャンセル',
        commit: 'エクスポートする',
        emit: 'export:loadouts',
      }
      this.$root.$emit('open:notification', notices)
    },
  },

}
</script>
