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
            'tb': 'inset 0 0 0 0 #e5e7eb , inset 0px -8px 6px -6px #e5e7eb, inset 0 0 0 0 #e5e7eb, inset 0px 8px 6px -6px #e5e7eb',
            'left': '0 9px 0 0 rgba(31, 73, 125, 0.8), 0 -9px 0 0 rgba(31, 73, 125, 0.8), 6px 0px 6px -6px rgba(31, 73, 125, 0.8), -6px 0px 6px -6px rgba(31, 73, 125, 0.8)',
            'x': '0 9px 0px 0px white, 0 -9px 0px 0px white, 12px 0 15px -4px rgba(31, 73, 125, 0.8), -12px 0 15px -4px rgba(31, 73, 125, 0.8)'
          },
          colors: {
            'tb-shadow': '#f4f6f9'
          }
      }
    }
}
