module.exports = {
    env: {
      node: true,
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