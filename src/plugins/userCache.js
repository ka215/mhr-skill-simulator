/**
 * CRUD system plugin for Cache Storage via Axios
 *
 * @since v0.1.6
 */
const userCache  = {
  install(Vue, options) {
    Vue.prototype.$userCache = {
      open: async function () {
        return await caches.open(options.cacheName).then(cache => cache)
      },
      save: async function (request, axiosResponse, cacheContent) {
        if (typeof request !== 'string') {
          request = this._createRequestObject(axiosResponse)
        }
        let response = this._createResponseObject(axiosResponse, cacheContent)
        await this.open()
        .then(cache => cache.put(request, response))
        .catch(error => console.error('Failed to save cache.', error))
      },
      /*
      modify: async function (pattern, axiosResponse) {
        console.log(pattern, axiosResponse)
      },
      * /
      find: async function (pattern) {
        let allKeys = this.keys(),
            cacheData = [],
            re = new RegExp(pattern, 'i')
        allKeys.forEach((k, v) => {
          if (re.test(k)) {
            console.log('find:', pattern, re, k, v)
          }
        })
        console.log(cacheData)
        return await this.find().then(keys => {
          let cacheData = []
          keys.forEach(request => {
            cacheData.push()
          })
        })
      },
      */
      loadAll: async function (callback=null) {
        return await this.open()
        .then(cache => cache.matchAll())
        .then(responses => {
          let cacheData = []
          if (responses && responses.length > 0) {
            responses.forEach(res => {
              res.json().then(json => {
                return cacheData.push(json)
              })
              Promise.resolve(res)
            })
          }
          return cacheData
        })
        .then(cacheData => {
          if (callback && typeof callback === 'function') {
            callback(cacheData)
          } else {
            return Promise.reject()
          }
          return Promise.resolve()
        })
        .catch(error => console.log('', error))
      },
      remove: async function (request, axiosResponse=null, callback=null) {
        if (typeof request !== 'string' && axiosResponse) {
          request = this._createRequestObject(axiosResponse)
        }
        await this.open()
        .then(cache => cache.delete(request))
        .then(result => {
          if (callback && typeof callback === 'function') {
            callback(result)
          }
          return Promise.resolve()
        })
        .catch(error => console.error('Failed to delete cache.', error))
      },
      keys: async function () {
        return await this.open()
        .then(cache => cache.keys())
        .then(keys => {
          let cacheNames = []
          keys.forEach(request => {
            cacheNames.push(request.url.replace(window.location.origin, ''))
          })
          return cacheNames
        })
        .catch(error => console.log(error))
      },
      _createRequestObject: function (axiosResponse) {
        let url = axiosResponse.config.baseURL + axiosResponse.config.url,
            payload = {method: 'get', body: axiosResponse.config.data}
        return new Request(url, payload)
      },
      _createResponseObject: function (axiosResponse, bodyContent) {
        let bom  = new Uint8Array([0xEF, 0xBB, 0xBF]),
            blob = new Blob([bom, JSON.stringify(bodyContent, null, 2)], {type: 'application/json'}),
        //let blob = new Blob([JSON.stringify(bodyData, null, 2)], {type: 'application/json'}),
            init = {status: axiosResponse.status, statusText: axiosResponse.statusText, headers: axiosResponse.headers}
        return new Response(blob, init)
      },
    }
  }
}

export default userCache