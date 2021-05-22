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
        params: {
          token: null,
        },
      })
    },
    // Retrieve all data in the specified table
    // @param kind string "weapons" or "armors" or "talismans" or "decorations" or "skills"
    // @param callback function
    retrieveData: function(kind, callback=null) {
      if (!['weapons', 'armors', 'talismans', 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo'].includes(kind)) {
        return false
      }
      if (this.$store.state[kind].length > 0 && callback && typeof callback === 'function') {
        return callback()
      }
      const instance = this.createAxios('//dev2.ka2.org/mhr/')// <- on the XAMPP only
      //const instance = this.createAxios('//ka2.org/mhr/')// for production
      instance.get(`index.php?tbl=${kind}`)
      .then(response => {
        //this.$store.state[kind] = response.data
        this.$store.dispatch('initData', {property: kind, data: response.data})
      })
      .catch(error => {
        console.error(`Failure to retrieve ${kind} data. (${error})`)
      })
      .finally(() => {
        if (callback && typeof callback === 'function') {
          callback()
        }
      })
    },
    // Load all master data as initializing application
    getMasterData: async function() {
      const instance = this.createAxios('//dev2.ka2.org/mhr/')// <- on the XAMPP only
      //const instance = this.createAxios('//ka2.org/mhr/')// for production
      const tables = [ 'weapons', 'armors', 'talismans', 'decorations', 'skills', 'skill_evaluation', 'weapon_meta', 'ammo' ]
      let remained = tables.concat(), rate = 0
      for (let table of tables) {
        await instance.get(`index.php?tbl=${table}`)
        .then(response => {
          this.$store.dispatch('initData', {property: table, data: response.data})
        })
        .catch(error => {
          console.error(`Failure to retrieve ${table} data. (${error})`)
        })
        .finally(() => {
          this.sleep(300).then(() => {
            remained.shift()
            rate = Math.ceil((1 - (remained.length / tables.length)) * 100)
            this.progress = rate >= 100 ? 100 : rate
          })
        })
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