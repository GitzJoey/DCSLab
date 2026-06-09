import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
const configFolder = fileURLToPath(new URL('.', import.meta.url))
  const env = loadEnv(mode, configFolder, '')

  return {
    plugins: [
      vue(),
      vueDevTools(),
      tailwindcss(),
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        '@midoneui/core': fileURLToPath(new URL('./src/components/ui', import.meta.url))
      },
    },
    server: {
      allowedHosts: true,
    }
  }
})