import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      // Redireciona chamadas de API para o servidor PHP
      '/app': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
  }
})
