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
  }
}