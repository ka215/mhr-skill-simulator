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
    createAxios: function() {
      //const BASE_URI = this.isLocalhost() ? '//localhost:8080/': `//${window.location.hostname}/mhr-simulator/dist/`
      const BASE_URI = this.isLocalhost() ? '//dev2.ka2.org/mhr-simulator/': `//${window.location.hostname}/mhr-simulator/dist/`
      return this.axios.create({
        baseURL: BASE_URI,
        headers: {
          'Content-Type': 'application/json;charset=utf-8',
          'Accept': 'application/json',
        },
        params: {
          token: null,
        },
      })
    },
    // Get data via rest
    getData: async function(...args) {
      const instance = this.createAxios()
      let query_str = '',
          queries = [],
          url = 'put'
      for (let [key, val] of Object.entries(args)) {
        queries.push(`${key}=${val}`)
      }
      query_str = '?' + encodeURI(queries.join('&'))
      if (!query_str) {
        url += query_str
      }
      console.log('putData::beforePOST:', url, args)
      instance.post(url, args)
      .then(response => {
        console.log('putData::afterPOST:', response.data, response)
      })
      .catch(error => {
        console.error(error)
      })
    },
  }
}