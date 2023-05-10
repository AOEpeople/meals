import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'https://meals.test/',
    "supportFile": "cypress/support/index.ts",
  },
  env: {
    "oauth_enable": true,
    "oauth_base_url": "https://login-staging.aoe.com",
    "oauth_realm": "aoe",
    "oauth_client_id": "aoe-meals-staging",
    "oauth_redirect_uri": "login/check-meals"
  },
})
