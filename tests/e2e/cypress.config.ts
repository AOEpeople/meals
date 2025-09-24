import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      on('task', {
        log(args) {
          console.log(...args);
          return null;
        }
      });
      return require('./cypress/plugins/index.js')(on, config)
    },
    supportFile: "cypress/support/index.ts",
  },
  env: {
    "baseUrl": 'https://meals.test/',
    "cookie_domain": 'meals.test',
    "oauth_enable": true,
    "oauth_base_url": "https://aoe.login.bare.id",
    "oauth_realm": "aoe-staging",
    "oauth_client_id": "aoe-meals-staging",
    "oauth_redirect_uri": "login/check-meals",
    "mailhog_url": "https://meals.ddev.site:8026",
    "ddev_test": true
  },
  defaultCommandTimeout: 5000,
  viewportWidth: 1360,
  viewportHeight: 800,
  screenshotOnRunFailure: true,
  video: true,
  trashAssetsBeforeRuns: true
})
