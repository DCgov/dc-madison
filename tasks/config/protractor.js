module.exports = function (grunt) {
  grunt.config.set('protractor', {
    options: {
      configFile: "protractor.conf.js", // Default config file
      keepAlive: false, // If false, the grunt process stops when the test fails.
      noColor: false // If true, protractor will not use colors in its output.
    },
    chrome: {
      options: {
        args: {
          sauceUser: process.env.SAUCE_USERNAME,
          sauceKey: process.env.SAUCE_ACCESS_KEY,
          browser: "chrome"
        }
      }
    },
    firefox: {
      options: {
        args: {
          sauceUser: process.env.SAUCE_USERNAME,
          sauceKey: process.env.SAUCE_ACCESS_KEY,
          browser: "firefox"
        }
      }
    },
    ie: {
      options: {
        args: {
          sauceUser: process.env.SAUCE_USERNAME,
          sauceKey: process.env.SAUCE_ACCESS_KEY,
          browser: "internet explorer"
        }
      }
    },
    safari: {
      options: {
        args: {
          sauceUser: process.env.SAUCE_USERNAME,
          sauceKey: process.env.SAUCE_ACCESS_KEY,
          browser: "safari"
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-protractor-runner');
};
