const { defineConfig } = require("cypress");

module.exports = defineConfig({
  projectId: 'aduh1c',
  e2e: {
    baseUrl: 'http://localhost:3000',
    viewportWidth: 1920,
    viewportHeight: 1080,
    setupNodeEvents(on, config) {

    },
  },
});
