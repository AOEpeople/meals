/** @type {import('tailwindcss').Config} */
export default {
    content: ['./src/**/*.{html,js,jsx,vue,ts,tsx}'],
    presets: [require('./node_modules/aoe-group-web-cd/aoe.config.js')],
    theme: {
        extend: {
            screens: {
                xs: '455px'
            },
            animation: {
                'spin-receiving': 'spin 4s reverse linear infinite',
                'spin-loading': 'loading 1.5s reverse ease-in-out infinite'
            },
            keyframes: {
                loading: {
                    '0%': {
                        transform: 'rotate(0deg)'
                    },
                    '100%': {
                        transform: 'rotate(720deg)'
                    }
                }
            },
            boxShadow: {
                tb: 'inset 0 0 0 0 #e5e7eb , inset 0px -8px 6px -6px #e5e7eb, inset 0 0 0 0 #e5e7eb, inset 0px 8px 6px -6px #e5e7eb',
                day: '0 4px #ffffff75,0 15px 35px #d8e1e9cc'
            },
            colors: {
                'tb-shadow': '#f4f6f9',
                triangle: '#585858 transparent transparent transparent'
            }
        }
    },
    plugins: []
};
