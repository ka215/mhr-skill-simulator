export default {
  methods: {
    // Determine the host and switch debug mode
    isLocalhost: function() {
      let hostName = document.location.hostname
      return hostName === 'localhost' || hostName === '127.0.0.1'
    }
  }
}