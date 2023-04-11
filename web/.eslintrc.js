module.exports = {
    env: {
      node: true,
    },
    parser: 'vue-eslint-parser',
    parserOptions: {
      parser: '@typescript-eslint/parser',
    },
    extends: [
      'eslint:recommended',
      'plugin:vue/vue3-recommended',
      'plugin:@typescript-eslint/recommended',
      'prettier'
    ],
    rules: {
      "vue/attribute-hyphenation": ["error", "always", {
        "ignore": [
          "htmlFor"
        ]
      }]
    }
  }