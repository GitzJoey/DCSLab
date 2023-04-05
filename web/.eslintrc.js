module.exports = {
    env: {
      node: true,
    },
    parser: '@typescript-eslint/parser',
    extends: [
      'eslint:recommended',
      'plugin:vue/vue3-recommended',
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