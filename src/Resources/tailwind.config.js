module.exports = {
  content: ["./src/**/*.{html,js,jsx,vue,ts,tsx}"],
  presets: [
      require("./node_modules/aoe-group-web-cd/aoe.config.js"),
  ],
    theme: {
      extend: {
          screens: {
              'xs': '455px'
          },
          animation: {
            'spin-receiving': 'spin 4s reverse linear infinite'
          },
          boxShadow: {
            'tb': 'inset 0 0 0 0 #e5e7eb , inset 0px -8px 6px -6px #e5e7eb, inset 0 0 0 0 #e5e7eb, inset 0px 8px 6px -6px #e5e7eb'
          },
          colors: {
            'tb-shadow': '#f4f6f9'
          }
      }
    }
}
