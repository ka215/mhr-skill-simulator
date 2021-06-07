//import userCache from './userCache.js'

export default {
  methods: {
    /**
     * Determine if the current host is a local host.
     *
     * @return bool
     */
    isLocalhost: function() {
      return /^(localhost|127\.0\.0\.1)$/.test(window.location.hostname)
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
      this.$userCache.loadAll(cacheData => {
        this.$store.dispatch('initData', {property: 'talismans', data: cacheData})
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
     * Generate a request object for the CacheAPI from the response transformed by Axios
     *
     * @param  object axiosResponse
     * @return object
     * /
    createRequestObject: function(axiosResponse) {
      let url = axiosResponse.config.baseURL + axiosResponse.config.url,
          payload = {method: axiosResponse.config.method, body: axiosResponse.config.data},
          requestObject = new Request(url, payload)
      return requestObject
    },
    /**
     * Generate a request object for the CacheAPI from the response transformed by Axios
     *
     * @param  object axiosResponse
     * @param  object bodyData
     * @return object
     * /
    createResponseObject: function(axiosResponse, bodyData) {
      let bom  = new Uint8Array([0xEF, 0xBB, 0xBF]),
          blob = new Blob([bom, JSON.stringify(bodyData, null, 2)], {type: 'application/json'}),
      //let blob = new Blob([JSON.stringify(bodyData, null, 2)], {type: 'application/json'}),
          init = {status: axiosResponse.status, statusText: axiosResponse.statusText, headers: axiosResponse.headers},
          responseObject = new Response(blob, init)
      return responseObject
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