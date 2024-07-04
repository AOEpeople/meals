const { initPlugin } = require('cypress-plugin-snapshots/plugin');
const cypressSplit = require('cypress-split');

module.exports = (on, config) => {
  initPlugin(on, config);
  cypressSplit(on, config);
  return config;
};
