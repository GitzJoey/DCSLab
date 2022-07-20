const { defineConfig } = require("cypress");

module.exports = defineConfig({
  projectId: 'aduh1c',
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
