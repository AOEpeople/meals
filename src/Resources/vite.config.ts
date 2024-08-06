import { fileURLToPath, resolve, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import symfony from 'vite-plugin-symfony'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import { dirname } from 'node:path'
import { rm } from 'node:fs/promises'

// https://vitejs.dev/config/
export default defineConfig({
  base: process.env.NODE_ENV === 'production' ? '/static/' : '/',
  plugins: [
    vue(),
    vueDevTools(),
    symfony({
      enforcePluginOrderingPosition: true,
      enforceServerOriginAfterListening: true,
      viteDevServerHostname: 'meals.test',
      originOverride: 'https://meals.test:5173'
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
  define: {
    'import.meta.env.VITE_ENV': JSON.stringify(process.env.NODE_ENV)
  },
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    origin: 'https://meals.test:5173',
    cors: true,
    headers: {
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
      'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization',
      'Access-Control-Allow-Origin': '*',
      'X-Content-Type-Options': 'nosniff'
    },
    proxy: {
      '/static/': 'https://localhost:5173'
    },
    // hmr: {
    //   host: 'meals.test',
    //   protocol: 'ws'
    // }
  },
  build: {
    outDir: '../../public/static',
    manifest: true,
    sourcemap: true,
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
          sourcemapPathTransform: (relativeSourcePath) => {
            return `src/${relativeSourcePath}`;
          },
        }
    }
  },
})
