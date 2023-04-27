module.exports = {
    extends: [
        // 'eslint:recommended',
        'plugin:vue/vue3-recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:tailwindcss/recommended',
    ],

    parser: "vue-eslint-parser",

    parserOptions: {
        parser: '@typescript-eslint/parser',
    },

    plugins: [
        '@typescript-eslint',
        'tailwindcss'
    ],

    root: true,

    rules: {
        // override/add rules settings here, such as:
        'vue/no-unused-vars': 'off',
        'vue/multi-word-component-names': 'off',
        'vue/require-prop-types': 'off',
        'vue/attribute-hyphenation': 'off',
        'vue/v-on-event-hyphenation': 'off',
        'vue/no-setup-props-destructure': 'off',
        'vue/no-mutating-props': 'off',
        '@typescript-eslint/no-this-alias': [
            'error',
            {
                'allowDestructuring': true, // Allow `const { props, state } = this`; false by default
                'allowedNames': ['vm'] // Allow `const vm= this`; `[]` by default
            }
        ],
        'tailwindcss/no-custom-classname': 'off',
        'tailwindcss/enforces-negative-arbitrary-values': 'off'
    },

    ignorePatterns: ['node_modules/', 'dist/', 'src/**/*.d.ts', '*.config.js', 'js/'],

    overrides: [
      {
        files: [
          '**/__tests__/*.{j,t}s?(x)',
          '**/tests/unit/**/*.spec.{j,t}s?(x)'
        ],
        env: {
          jest: true
        }
      }
    ]
}
