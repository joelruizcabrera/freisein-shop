const { defineConfig } = require('cypress')

module.exports = defineConfig({
    e2e: {
        baseUrl: "https://shopware.dev.die-etagen.de",
        port: 6969,
        supportFile: "cypress/support/index.js",
        specPattern: "cypress/integration/*.spec.{js,ts}"
    },
})
