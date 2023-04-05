module.exports = {
    env: {
      node: true,
    },
    parserOptions: {
      ecmaVersion: 2017,
    },
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