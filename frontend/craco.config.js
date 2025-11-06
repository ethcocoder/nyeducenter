module.exports = {
  devServer: {
    host: '0.0.0.0',
    port: 3000,
    allowedHosts: 'all',
    proxy: {
      '/api': {
        target: 'http://192.168.50.76:5001',
        changeOrigin: true,
      },
    },
  },
}; 