module.exports = {
  content: ["./src/**/*.{html,js,jsx,vue,ts,tsx}"],
  presets: [
      require("./node_modules/aoe-group-web-cd/aoe.config.js"),
  ],
    theme: {
      extend: {
          screens: {
              'xs': '455px'
          }
      }
    }
}
