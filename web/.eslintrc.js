module.exports = {
    root: true,
    env: {
        node: true,
    },
    parser: 'vue-eslint-parser',
    parserOptions: {
        parser: '@typescript-eslint/parser',
        ecmaVersion: 2020,
        sourceType: 'module',
    },
    extends: [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:vue/vue3-recommended',
        'prettier',
    ],
    rules: {

    },
    overrides: [
        {
            files: ['**/*.ts', '**/*.tsx'],
            rules: {

            },
        },
    ],
    ignorePatterns: [

    ]
};
