const cypressSplit = require('cypress-split');

module.exports = (on, config) => {
  cypressSplit(on, config);
  return config;
};
