import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/Api': {
        target: 'https://www.bling.com.br',
        changeOrigin: true,
        secure: false,
      }
    }
  }
})

