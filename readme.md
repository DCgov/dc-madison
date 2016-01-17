# Madison

[![Build Status](https://api.travis-ci.org/DCgov/dc-madison.svg?branch=master)](https://travis-ci.org/DCgov/dc-madison)

Madison is the open-source, collaborative document editing platform that powers [drafts.dc.gov](https://drafts.dc.gov).

The site is in beta, so if you run into any problems or have questions, please open an issue and we will try to respond as soon as possible.

We have forked the [Madison master repository](https://github.com/opengovfoundation/madison), which remains in active development. You are encouraged to submit (non-DC specific) [issues](https://github.com/opengovfoundation/madison/issues) and [pull requests](https://github.com/opengovfoundation/madison/compare?expand=1) there instead.

## Installation

Please take a look at the [Madison Documentation](https://github.com/opengovfoundation/madison/tree/master/docs) for how to install Madison.

## Testing Suite

To run the automated test suite locally you will first need to use the
webdriver manager provided by protractor. This allows you to install and run
selenium through a simple cli.

```
$ npm install -g protractor
$ webdriver-manager update
```

To start the selenium server:

```
$ webdriver-manager start
```

Once that is installed, you should be able to run tests for various browsers
using `grunt test_{browser name}` like so:

```
$ grunt test_chrome
$ grunt test_firefox
$ grunt test_ie
```

A database is automatically created in mysql for testing, so no need to create
one.

You will need to have drivers installed for these browsers to test them locally.
When a pull request is created, the tests will be run for all browsers using
[Sauce Labs](https://saucelabs.com/).


## How to help

* Open an issue, claim an issue, comment on an issue, or submit a pull request to resolve one
* Document Madison - wiki documentation, installation guide, or docblocks internally
* Clean up existing code - I'm sure we've taken shortcuts or added lazy code somewhere.
