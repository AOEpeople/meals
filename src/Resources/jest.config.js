/** @type {import('ts-jest').JestConfigWithTsJest} */
module.exports = {
    verbose: true,
    preset: 'ts-jest',
    testEnvironment: 'jsdom',
    testEnvironmentOptions: {
        customExportConditions: ['node', 'node-addons']
    },
    moduleNameMapper: {
        'tools(.*)$': '<rootDir>/src/tools/$1',
        'vue-i18n': 'vue-i18n/dist/vue-i18n.runtime.esm-bundler.js',
        '^@/(.*)$': '<rootDir>/src/$1'
    },
    transform: {
        '^.+\\.vue$': '@vue/vue3-jest',
        '^.+\\js$': 'babel-jest',
        '^.+\\.tsx?$': [
            'ts-jest',
            {
                tsconfig: './tsconfig.json'
            }
        ],
        '^.+\\.(css|less|sass|scss|png|jpg|gif|ttf|woff|woff2|svg)$': 'jest-transform-stub'
    },
    transformIgnorePatterns: ['/node_modules/'],
    moduleFileExtensions: ['vue', 'js', 'jsx', 'json', 'ts', 'tsx', 'node'],
    globals: {
        '@vue/vue3-jest': {
            compilerOptions: {
                propsDestructureTransform: true,
                refTransform: false
            }
        }
    },
    setupFilesAfterEnv: ['<rootDir>/tests/unit/setup-jest.ts']
};