import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'https://meals.test/',
    setupNodeEvents(on, config) {
        return require('./cypress/plugins/index.js')(on, config)
    },
    supportFile: "cypress/support/index.ts",
  },
  env: {
    "baseUrl": 'https://meals.test/',
    "oauth_enable": true,
    "oauth_base_url": "https://aoe.login.bare.id",
    "oauth_realm": "aoe-staging",
    "oauth_client_id": "aoe-meals-staging",
    "oauth_redirect_uri": "login/check-meals",
    "mailhog_url": "https://meals.ddev.site:8026"
  },
  defaultCommandTimeout: 5000,
  viewportWidth: 1360,
  viewportHeight: 800,
})
