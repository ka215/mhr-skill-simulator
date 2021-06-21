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
      <v-card>
        <v-card-title
          :class="[{'amber--text': $vuetify.theme.isDark}, {'blue-grey--text': !$vuetify.theme.isDark}, {'text--accent-4': $vuetify.theme.isDark}, {'text--darken-4': !$vuetify.theme.isDark}]"
        >{{ labels.title }}</v-card-title>
        <v-divider />
        <v-card-text
          class="py-3 px-4"
          v-html="labels.contents"
        ></v-card-text>
        <v-card-text
          class="py-3 px-5"
        >
          <v-text-field
            v-model="loadoutsName"
            dense
            :label="labels.name"
            :placeholder="labels.defaultLoadouts"
            outlined
          ></v-text-field>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn
            text
            @click="dialog = isDuring = false"
          >{{ labels.close }}</v-btn>
          <template v-if="withUpdate">
            <v-btn
              text
              @click="commit('update')"
            >{{ labels.update }}</v-btn>
          </template>
          <v-btn
            text
            @click="commit('register')"
          >{{ labels.register }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'LoadoutsEditor',

  components: {
    //
  },

  data: () => ({
    dialog: false,
    labels: {
      title: 'マイセットの新規登録',
      contents: null,
      name: 'マイセット名',
      defaultLoadouts: 'マイセット名',
      close: '閉じる',
      update: '更新する',
      register: '登録する',
    },
    loadoutsName: null,
    withUpdate: false,
    isDuring: false,
  }),

  created() {
    this.$root.$on('save:loadouts', (args) => {
      if (this.dialog || this.isDuring) {
        return false
      }
      this.isDuring = true
      let _cl = this.$store.getters.itemsById('loadouts', this.$store.state.now_loadouts)
      //console.log('LoadoutsEditor.vue::save:loadouts', args, _cl, this.dialog, this.isDuring)
      switch (args.action) {
        case 'register':
          this.withUpdate = false
          this.labels.contents = '現在の装備をマイセットに追加します。マイセット名の変更も可能です。'
          break
        case 'update':
          this.withUpdate = true
          this.labels.title = 'マイセットの更新／登録'
          this.labels.contents = '現在の装備で読み込まれているマイセットを上書きします。マイセット名の変更も可能です。<br>新たなマイセットとして追加する場合は「登録する」を実行してください。'
          if (_cl && _cl.name) {
            this.loadoutsName = _cl.name
          }
          break
      }
      this.dialog = true
    })
  },

  mounted() {
    //
  },

  computed: {
    dialogWidth () {
      switch (this.$vuetify.breakpoint.name) {
        case 'xs': return `${this.$vuetify.breakpoint - 30}px`
        case 'sm':
        default: return '600px'
      }
    },
  },

  methods: {
    setLoadoutsData: function(name=null) {
      const kinds = ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']
      let data = {
        'name':           name || 'マイセット',
        'weapon_id':      0,
        'weapon_slots':   {},
        'head_id':        0,
        'head_lv':        1,
        'head_slots':     {},
        'chest_id':       0,
        'chest_lv':       1,
        'chest_slots':    {},
        'arms_id':        0,
        'arms_lv':        1,
        'arms_slots':     {},
        'waist_id':       0,
        'waist_lv':       1,
        'waist_slots':    {},
        'legs_id':        0,
        'legs_lv':        1,
        'legs_slots':     {},
        'talisman_id':    0,
        'talisman_slots': {},
        'skills':         {},
        'user_info':      {},
      }
      kinds.forEach(kind => {
        let _slots = null
        if (/^(weapon|talisman)$/.test(kind)) {
          if (this.$store.getters.equipmentExists(kind)) {
            data[`${kind}_id`] = this.$store.getters.equipmentKindOf(kind, 'id')
            _slots = this.$store.getters.currentSlotsKindOf(kind)
            data[`${kind}_slots`] = Object.keys(_slots).length > 0 ? _slots: {}
          }
        } else {
          if (this.$store.getters.equipmentExists(kind)) {
            data[`${kind}_id`] = this.$store.getters.equipmentKindOf(kind, 'id')
            data[`${kind}_lv`] = this.$store.getters.armorLevelKindOf(kind)
            _slots = this.$store.getters.currentSlotsKindOf(kind)
            data[`${kind}_slots`] = Object.keys(_slots).length > 0 ? _slots: {}
          }
        }
      })
      data.skills = this.$store.state.aggs
      data.user_info = {
        'user_name': null,
        'ip_address': null,
        'ua': window.navigator.userAgent,
        'referrer': window.location.href,
        'signature': window.location.origin,
      }
      return data
    },
    commit: function(action) {
      const newLoadouts = this.setLoadoutsData(this.loadoutsName)
      this.dialog = false
      if ('register' === action) {
        //console.log('LoadoutsEditor.vue::commit:Before loadouts insertion:', newLoadouts)
        this.saveData('post', {table: 'loadouts', data: newLoadouts}, (response) => {
          let notices = {
            title: null,
            messages: [],
          }
          if (response.data.state == 201) {
            newLoadouts.id = response.data.id
            delete newLoadouts.user_info
            // Save into cache storage
            let request = `index.php?tbl=loadouts&filters[id]=${response.data.id}`,
                cacheContent = newLoadouts
            this.$userCache.save(request, response, cacheContent)
            // Update Vuex Store
            this.$store.dispatch('addData', {property: 'loadouts', data: newLoadouts})
            this.$store.dispatch('initData', {property: 'now_loadouts', data: response.data.id})
            
            notices.title = '通知'
            notices.messages = [ 'マイセットに追加されました。', '追加したマイセットはメニューの「マイセット」から管理できます。' ]
          } else {
            notices.title = 'エラー'
            notices.messages = [ 'マイセットの登録に失敗しました。', 'もう一度お試しください。' ]
          }
          //console.log('LoadoutsEditor.vue::commit:After loadouts insertion:', response, notices)
          this.isDuring = false
          this.$root.$emit('open:notification', notices)
        })
      } else
      if ('update' === action) {
        const targetLoadoutsId = this.$store.state.now_loadouts
        const params = {
          table: 'loadouts',
          data: newLoadouts,
          conditions: [ ['id', '=', targetLoadoutsId] ],
          operator: 'and',
        }
        //console.log('LoadoutsEditor.vue::commit:Before loadouts updating:', newLoadouts, targetLoadoutsId, params)
        this.saveData('put', params, (response) => {
          let notices = {
            title: null,
            messages: [],
          }
          if (response.data.state == 201) {
            newLoadouts.id = targetLoadoutsId
            delete newLoadouts.user_info
            // Save into cache storage
            let request = `index.php?tbl=loadouts&filters[id]=${targetLoadoutsId}`,
                cacheContent = newLoadouts
            this.$userCache.save(request, response, cacheContent)
            // Refresh Vuex Store
            this.$userCache.find('loadouts', cacheData => {
              this.$store.dispatch('initData', {property: 'loadouts', data: cacheData})
            })
            
            notices.title = '通知'
            notices.messages = [ 'マイセットを更新しました。', '更新したマイセットはメニューの「マイセット」から管理できます。' ]
          } else {
            notices.title = 'エラー'
            notices.messages = [ 'マイセットの更新に失敗しました。', 'もう一度お試しください。' ]
          }
          //console.log('LoadoutsEditor.vue::commit:After loadouts updating:', response, notices)
          this.isDuring = false
          this.$root.$emit('open:notification', notices)
        })
      }
    },
  },

}
</script>