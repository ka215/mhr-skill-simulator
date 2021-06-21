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
      get: async function (request, callback=null) {
        return await this.open()
        .then(cache => cache.match(request))
        .then(response => response.json())
        .then(data => {
          if (callback && typeof callback === 'function') {
            callback(data)
          } else {
            return Promise.reject()
          }
          return Promise.resolve()
        })
        .catch(error => console.log('', error))
      },
      find: async function (pattern, callback=null) {
        const re = new RegExp(pattern, 'i')
        const matchRequest = await this.keys()
        .then(allKeys => {
          let matchRequest = []
          allKeys.forEach(key => {
            if (re.test(key)) {
              matchRequest.push(key)
            }
          })
          return matchRequest
        })
        let cacheData = []
        if (matchRequest.length > 0) {
          matchRequest.forEach(async request => {
            await this.get(request, (data) => {
              cacheData.push(data)
            })
          })
        }
        if (callback && typeof callback === 'function') {
          callback(cacheData)
        }
      },
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
        //console.log('_createResponseObject:', init, blob)
        return new Response(blob, init)
      },
    }
  }
}

export default userCache