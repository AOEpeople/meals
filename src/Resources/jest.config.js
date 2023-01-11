/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'node',
  moduleNameMapper: {
    "tools(.*)$": "<rootDir>/src/tools/$1",
    'vue-i18n': 'vue-i18n/dist/vue-i18n.runtime.esm-bundler.js',
  },
};