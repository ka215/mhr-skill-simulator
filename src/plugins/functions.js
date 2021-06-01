export default {
  methods: {
    // Determine the host and switch debug mode
    isLocalhost: function() {
      return /^(localhost|127\.0\.0\.1)$/.test(window.location.hostname)
    },
    // Sleep at the milliseconds specified
    // Usage: this.sleep(300).then(() => { Do something... })
    sleep: async function(sec=1000) {
      return await new Promise(resolve => setTimeout(resolve, sec))
    },
    // Create axios instance
    createAxios: function(overrideURL=null) {
      //const DEFAULT_BASE_URL = this.isLocalhost() ? 'localhost:8080/': `${window.location.hostname}/mhr/v${process.env.VUE_APP_VERSION}/`
      //const DEFAULT_BASE_URL = this.isLocalhost() ? 'dev2.ka2.org/mhr-simulator/': `${window.location.hostname}/mhr-simulator/v${process.env.VUE_APP_VERSION}/`
      const DEFAULT_BASE_URL = this.isLocalhost() ? 'localhost:8080/': `${window.location.hostname}/mhr/v${process.env.VUE_APP_VERSION}/`
      let connectURL = overrideURL || '//' + DEFAULT_BASE_URL
      return this.axios.create({
        baseURL: connectURL,
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          'Accept': 'application/json',
        },
        //withCredentials: true,
        params: {
          token: null,
        },
      })
    },
    // Retrieve all data in the specified table
    // @param string    kind     "weapons" or "armors" or "talismans" or "decorations" or "skills"
    // @param function? callback 
    retrieveData: async function(kind, callback=null) {
      if (!['weapons', 'armors', 'talismans', 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo'].includes(kind)) {
        return false
      }
      if (this.$store.state[kind].length > 0 && callback && typeof callback === 'function') {
        return callback()
      }
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      await instance.get(`index.php?tbl=${kind}`)
      .then(response => {
        //this.$store.state[kind] = response.data
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
    },
    // Load all master data as initializing application
    getMasterData: async function() {
      //console.log('getMasterData:', process.env.VUE_APP_PROD_URL)
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      const tables = [ 'weapons', 'armors', 'talismans', 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo' ]
      let remained = tables.concat(),
          rate = 0,
          requestPath = ''
      for (let table of tables) {
        requestPath = `index.php?tbl=${table}`
        if (table === 'talismans') {
          requestPath += '&filters[disabled]=false'
        }
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
    // Save the data in a database
    // @param string    method    "post" for inserting, "put" for updating, "delete" for deleting, "patch" for patching
    // @param object    params    e.g. {table: "talismans", data: <object:talismanData>} or {table: "talismans", action: "delete", data: {id: <int:n>}}
    // @param function? callback  Callback function after a normal response
    // @param function? always    Callback function that always executes regardless of the result of the response
    saveData: async function(method, params, callback=null, always=null) {
      const instance = this.createAxios(process.env.VUE_APP_PROD_URL)
      method = ['post', 'put', 'delete', 'patch'].includes(method) ? method: 'post'
      await instance[method]('index.php', {params: params})
      .then(response => {
        console.log(response)
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
    getEquipmentKind: function(itemObject) {
      if (Object.prototype.hasOwnProperty.call(itemObject, 'type')) {
        return 'weapon'
      } else
      if (Object.prototype.hasOwnProperty.call(itemObject, 'part')) {
        return ['head', 'chest', 'arms', 'waist', 'legs'][itemObject.part]
      } else {
        return 'talisman'
      }
    },
    getArmorPartIndex: function(partString) {
      return ['head', 'chest', 'arms', 'waist', 'legs'].indexOf(partString)
    },
    isArmor: function(partString) {
      return ['head', 'chest', 'arms', 'waist', 'legs'].includes(partString)
    },
    // Get defined equipment types
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
    // Get display name of element
    getElementName: function(value) {
      const elements = [
        'なし',/*（無属性）*/ '火', '水', '雷', '氷', '龍', '毒', '麻痺', '睡眠', '爆破'
      ]
      return elements[value]
    },
  }
}