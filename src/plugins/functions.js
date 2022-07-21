export default {
  methods: {
    /**
     * Determine if the current host is a local host.
     *
     * @return bool
     */
    isLocalhost: function() {
      //return /^(localhost|127\.0\.0\.1)$/.test(window.location.hostname)
      return true
    },
    /**
     * Sleep at the milliseconds specified.
     *
     * @usage `this.sleep(300).then(() => { Do something... })`
     */
    sleep: async function(sec=1000) {
      return await new Promise(resolve => setTimeout(resolve, sec))
    },
    /**
     * Remove duplicate elements in an array to get an array of unique values only.
     *
     * @return array - A new array different from the one given as argument
     */
    arrayUnique: function(array) {
      let a = array.concat()
      for (let i = 0; i < a.length; ++i) {
        for (let j = i + 1; j < a.length; ++j) {
          if (a[i] === a[j])
            a.splice(j--, 1)
        }
      }
      return a
    },
    /**
     * Create axios instance.
     *
     * @param  string overrideURL 
     * @param  object addOptions  
     * @return object             - An instance of Axios
     */
    createAxios: function(overrideURL=null, addOptions=null) {
      //const DEFAULT_BASE_URL = this.isLocalhost() ? 'localhost:8080/': `${window.location.hostname}/mhr/v${process.env.VUE_APP_VERSION}/`
      //const DEFAULT_BASE_URL = this.isLocalhost() ? 'dev2.ka2.org/mhr-simulator/': `${window.location.hostname}/mhr-simulator/v${process.env.VUE_APP_VERSION}/`
      const DEFAULT_BASE_URL = this.isLocalhost() ? 'localhost:8080/': `${window.location.hostname}/mhr/v${process.env.VUE_APP_VERSION}/`
      let connectURL = overrideURL || '//' + DEFAULT_BASE_URL
      //console.log('createAxios:', connectURL, overrideURL)
      const DEFAULT_OPTIONS = {
        baseURL: connectURL,
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          'Accept': 'application/json',
        },
        params: {
          token: null,
        },
      }
      let options = addOptions ? Object.assign(DEFAULT_OPTIONS, addOptions): DEFAULT_OPTIONS
      return this.axios.create(options)
    },
    /**
     * Retrieve all data in the specified table.
     *
     * @deprecated since v0.1.6
     *
     * @param  string     kind     "weapons", "armors", "talismans", "decorations", "skills", "skill_evaluation", "weapon_meta", or "ammo"
     * @param  function?  callback 
     * @return void
     */
    dep_retrieveData: async function(kind, callback=null) {
      if (!['weapons', 'armors', 'talismans', 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo'].includes(kind)) {
        return false
      }
      if (this.$store.state[kind].length > 0 && callback && typeof callback === 'function') {
        return callback()
      }
      //console.log('dep_retrieveData:', process.env.VUE_APP_PROD_URL)
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      let requestPath = `index.php?tbl=${kind}`
      if (kind === 'talismans') {
        // まずはcacheから取得を試みる
        this.userCache(cache => {
          cache.keys().then(keys => {
            let cacheNames = []
            keys.forEach(request => {
              if (/\/talisman\/\d{1,}$/.test(request.url)) {
                cacheNames.push(request.url.replace(window.location.origin, ''))
              }
            })
            return cacheNames
          }).then(cacheNames => {
            //console.log('retrieveData::talismans:_1:', cacheNames)
            let cacheData = []
            cacheNames.forEach(requestURL => {
              cache.match(requestURL).then(response => response.json())
              .then(response => {
                cacheData.push(response)
              })
            })
            return cacheData
          }).then(cacheData => {
            console.log('retrieveData::talismans:', cacheData)
            this.$store.dispatch('initData', {property: kind, data: cacheData})
          }).catch(error => {
            console.error(`Failure to retrieve ${kind} data from caches.`, error)
          }).finally(() => {
            if (callback && typeof callback === 'function') {
              callback()
            }
          })
        })
        //requestPath += '&filters[disabled]=false'
      } else {
        await instance.get(requestPath)
        .then(response => {
          this.$store.dispatch('initData', {property: kind, data: response.data})
        })
        .catch(error => {
          console.error(`Failure to retrieve ${kind} data.`, error)
        })
        .finally(() => {
          if (callback && typeof callback === 'function') {
            callback()
          }
        })
      }
    },
    /**
     * Load all master data as initializing application
     *
     * @since 0.1.6 - Excluded Talisman data acquisition
     *
     * @return void
     */
    getMasterData: async function() {
      //console.log('getMasterData:', process.env.VUE_APP_PROD_URL)
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      const tables = [ 'weapons', 'armors',/* 'talismans',*/ 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo' ]
      let remained = tables.concat(),
          rate = 0,
          requestPath = ''
      for (let table of tables) {
        requestPath = `index.php?tbl=${table}`
        /*
        if (table === 'talismans') {
          requestPath += '&filters[disabled]=false'
        }
        */
        await instance.get(requestPath)
        .then(response => {
          this.$store.dispatch('initData', {property: table, data: response.data})
        })
        .catch(error => {
          console.error(`Failure to retrieve ${table} data.`, error)
        })
        .finally(() => {
          this.sleep(100).then(() => {
            remained.shift()
            rate = Math.ceil((1 - (remained.length / tables.length)) * 100)
            this.progress = rate >= 100 ? 100 : rate
          })
        })
      }
    },
    /**
     * Load all user data from cache storage
     *
     * @since 0.1.6
     *
     * @return void
     */
    loadUserData: function() {
      /*
      this.$userCache.loadAll(cacheData => {
        this.$store.dispatch('initData', {property: 'talismans', data: cacheData})
      })
      */
      this.$userCache.find('talismans', cacheData => {
        this.$store.dispatch('initData', {property: 'talismans', data: cacheData})
      })
      this.$userCache.find('loadouts', cacheData => {
        this.$store.dispatch('initData', {property: 'loadouts', data: cacheData})
      })
    },
    /**
     * Save the data in a database via axios.
     *
     * @param  string     method        "post" for inserting, "put" for updating, "delete" for deleting, "patch" for patching
     * @param  object     params        e.g. {table: "talismans", data: <object:talismanData>} or {table: "talismans", action: "delete", data: {id: <int:n>}}
     * @param  function?  callback      Callback function after a normal response on `then`
     * @param  function?  interceptors  Callback function intercepts the responses before they are handled by `then`
     * @param  function?  always        Callback function that always executes regardless of the result of the response
     * @return void
     */
    saveData: async function(method, params, callback=null, interceptors=null, always=null) {
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      instance.interceptors.response.use(response => {
        if (interceptors && typeof interceptors === 'function') {
          interceptors(response)
        }
        return response
      }, error => {
        return Promise.reject(error)
      })
      method = ['post', 'put', 'delete', 'patch'].includes(method) ? method: 'post'
      await instance[method]('index.php', {params: params})
      .then(response => {
        if (callback && typeof callback === 'function') {
          callback(response)
        }
      })
      .catch(error => {
        console.error('Failure to insert data.', error)
      })
      .finally(() => {
        if (always && typeof always === 'function') {
          always()
        }
      })
    },
    /**
     * Check data validity and optimize that for this system.
     *
     * @param  string dataType - Vuex Store Name
     * @param  object data     - Parsed JSON File
     * @return object          - Return empty object if invalid data
     */
    sanitizeData: function(dataType, data) {
      const requiredColumns = {
        'talismans': [
          'name',   'rarity', 'slot1', 'slot2', 'slot3',
          'skills', 'worth',  'id', 'slots', 'skills_text',
        ],
        'loadouts': [
          'id', 'name', 
          'weapon_id',               'weapon_slots',
          'head_id',     'head_lv',  'head_slots',
          'chest_id',    'chest_lv', 'chest_slots',
          'arms_id',     'arms_lv',  'arms_slots',
          'waist_id',    'waist_lv', 'waist_slots',
          'legs_id',     'legs_lv',  'legs_slots',
          'talisman_id',             'talisman_slots',
          'skills',
        ],
      }
      let checked = Object.keys(data).filter(key => requiredColumns[dataType].includes(key))
      if (checked.length == requiredColumns[dataType].length) {
        // Sanitizes valid data
        return checked.reduce((acc, cur) => {
          acc[cur] = data[cur]
          return acc
        }, {})
      } else {
        // Invalid data
        return {}
      }
    },
    /**
     * Store the imported JSON file in the Vuex store and Cache Storage.
     *
     * @param  string dataType - Vuex Store Name
     * @param  object data     - Parsed JSON File
     * @return void
     */
    import: async function(dataType, data) {
      //console.log('import::_1:%s', dataType, data)
      let notices = {
            title: '通知',
            messages: [],
            close: '閉じる',
            emit: null,
          }
      if (Array.isArray(data) && data.length > 0) {
        let throughputs = [],
            throughput  = 0
        data.forEach(oneData => {
          let _data = this.sanitizeData(dataType, oneData)
          if (Object.keys(_data).length > 0) {
            throughputs.push({
              request: `index.php?tbl=${dataType}&filters[id]=${_data.id}`,
              response: { status: 200, statusText: 'OK', headers: { 'content-length': JSON.stringify(_data).length, 'content-type': 'application/json; charset=utf-8' } },
              data: _data
            })
          }
        })
        //console.log('import::_2:', throughputs)
        await Promise.all(throughputs.map(async item => await this.$userCache.save(item.request, item.response, item.data).then(() => {
          //console.log('import::_3:', arguments[0], arguments[1].length)
          this.$store.dispatch('upsertData', { property: dataType, data: item.data })
          throughput++
        })))
        //console.log('import::_4:%d', throughput)
        if (data.length == throughput) {
          // Fully complete
          notices.messages.push('インポートが完了しました。')
        } else
        if (throughput > 0) {
          // Limited completion
          notices.messages.push('インポートが完了しました。', 'しかし、いくつかのデータのインポートに失敗しました。')
        } else {
          // All failed
          notices.title = 'エラー'
          notices.messages.push('インポートに失敗しました。', 'JSONデータの内容を確認してやり直してください。')
        }
      } else {
        notices.title = 'エラー'
        notices.messages.push('インポート用のデータが不正です。')
      }
      //console.log('import::_5:', notices)
      this.$root.$emit('open:notification', notices)
    },
    /**
     * Export the contents of a specified Vuex store in JSON format.
     *
     * @param  string dataType - Vuex Store Name
     * @return void
     */
    export: function(dataType) {
      const data = JSON.stringify(this.$store.state[dataType]),
            blob = new Blob([data], {type: 'text/plain'}),
            evnt = document.createEvent('MouseEvents'),
            a = document.createElement('a')
      a.download = `mhrss-${dataType}.json`
      a.href = window.URL.createObjectURL(blob)
      a.dataset.downloadurl = [/*'text/json'*/'application/json', a.download, a.href].join(':')
      evnt.initEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null)
      a.dispatchEvent(evnt)
    },
    /**
     * Returns which equipment type the object of the equipment item is.
     * (: 装備アイテムのオブジェクトがどの装備タイプであるかを返します。
     *
     * @param  object
     * @return string - If the equipment kind cannot be determined, an empty string will be returned.
     */
    getEquipmentKind: function(itemObject) {
      if (typeof itemObject !== 'object') {
        return ''
      } else
      if (Object.prototype.hasOwnProperty.call(itemObject, 'type')) {
        return 'weapon'
      } else
      if (Object.prototype.hasOwnProperty.call(itemObject, 'part')) {
        return ['head', 'chest', 'arms', 'waist', 'legs'][itemObject.part]
      } else
      if (Object.prototype.hasOwnProperty.call(itemObject, 'worth')) {
        return 'talisman'
      } else {
        return ''
      }
    },
    /**
     * Returns whether the part name is an armor or not.
     *
     * @param string
     * @return bool
     */
    isArmor: function(partString) {
      return ['head', 'chest', 'arms', 'waist', 'legs'].includes(partString)
    },
    /**
     * Get the index number of the armor part.
     * (: 防具の部位のインデックス番号を取得します。
     *
     * @param  string
     * @return int|bool - If the index number cannot be obtained, it returns false as a boolean value,
     *                    so it is necessary to match it with "===" when judging.
     */
    getArmorPartIndex: function(partString) {
      return this.isArmor(partString) ? ['head', 'chest', 'arms', 'waist', 'legs'].indexOf(partString): null
    },
    /**
     * Retrieve a defined equipment kinds.
     *
     * @param string type   - Only "weapons", "armor", or "talismans" are allowed.
     * @param int?   kind   - You can optionally specify an index number for weapon types and armor parts.
     * @return array|string - If the second argument is specified, a string will be returned. If it is omitted,
     *                        an array is returned.
     */
    getEquipType: function(type, kind=null) {
      const preset = {
        weapon: [
          '大剣',
          '太刀',
          '片手剣',
          '双剣',
          '鎚',// 'ハンマー',
          '狩猟笛',
          '鎗',// 'ランス',
          '銃鎗',// 'ガンランス',
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
      return kind === null ? preset[type]: preset[type][kind]
    },
    /**
     * Retrive the display name of element.
     *
     * @param  int elmIndex - Index number of the element.
     * @return string
     */
    getElementName: function(elmIndex) {
      const elements = [
        'なし',/*（無属性）*/ '火', '水', '雷', '氷', '龍', '毒', '麻痺', '睡眠', '爆破'
      ]
      return elements[elmIndex]
    },
  }
}