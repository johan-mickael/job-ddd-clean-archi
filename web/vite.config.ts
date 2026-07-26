import path from 'node:path'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      // The Symfony API is served by the `php` container (see compose.yaml).
      // FrankenPHP/Caddy force-redirects :80 to :443, so the target has to
      // be https - proxying to http just gets a 308 back, not JSON. `secure:
      // false` accepts the container's self-signed local dev certificate.
      '/api': {
        target: 'https://localhost',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
