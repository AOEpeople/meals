import { fileURLToPath, resolve, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import symfony from 'vite-plugin-symfony'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import { dirname } from 'node:path'
import { rm } from 'node:fs/promises'

const port = 5173;
const origin = `${process.env.DDEV_PRIMARY_URL}:${port}`;

// https://vitejs.dev/config/
export default defineConfig({
  // base: process.env.NODE_ENV === 'production' ? '/static/' : '/',
  base: '/static/',
  mode: 'production',
  plugins: [
    vue(),
    vueDevTools(),
    symfony({
      // debug: true,
      enforcePluginOrderingPosition: true,
      enforceServerOriginAfterListening: false,
      viteDevServerHostname: 'meals.test',
      // originOverride: 'https://meals.test'
    }),
    VueI18nPlugin({
      include: resolve(dirname(fileURLToPath(import.meta.url)), './src/locales/**'),
      jitCompilation: true
    }),
    {
      name: "Cleaning assets folder",
      async buildStart() {
        await rm(resolve(__dirname, '../../public/static'), { recursive: true, force: true });
      }
    }
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  // server: {
  //   host: true,
  //   port: port,
  //   strictPort: true,
  //   origin: 'https://meals.test:5173',
  //   cors: true,
  //   headers: {
  //     'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
  //     'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization',
  //     'Access-Control-Allow-Origin': '*',
  //     'X-Content-Type-Options': 'nosniff'
  //   },
  //   // hmr: {
  //   //   host: 'meals.test',
  //   //   protocol: 'ws'
  //   // }
  // },
  build: {
    outDir: '../../public/static',
    manifest: true,
    rollupOptions: {
        input: {
            /* relative to the root option */
            app: "./src/main.ts",

            /* you can also provide [s]css files */
            theme: "./style/main.css"
        },
        output: {
          chunkFileNames: '[name]-[hash].js',
          entryFileNames: '[name]-[hash].js',
          assetFileNames: '[name]-[hash].[ext]',
          manualChunks: {
            vue: ['vue']
          }
        }
    }
  },
})
