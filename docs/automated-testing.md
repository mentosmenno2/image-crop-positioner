# Automated testing

When you create a pull request, [GitHub Actions](https://github.com/mentosmenno2/image-crop-positioner/actions) will perform some automatic tests to make sure your code complies with the coding standards.
To prevent waiting for GitHub actions to run all checks, you can also run the tests locally.

## PHP testing

PHP has tests in place to verify coding standards, do some basic static analysis, and check if there is documentation present for complex functions.
To run the tests, you can use the command to test everything, or run tests separately.

```sh
# All tests at once
composer run test

# # Every test separately
composer run test:composer
composer run test:phpcs
composer run test:psalm
composer run test:docs
```

You can also try to quickly fix some coding standards issues.
But this won't solve all issues.

```sh
composer run fix
```

## NPM testing

There are also tests to verify the SASS and Javascript code.
It uses linters to do so:

- Styleint: Styleint is used, rules are set in `.stylelintrc.json`. View all [doc rules](https://stylelint.io/user-guide/rules/list)
- JS: eslint is used, rules are set in `.eslintrc.json`. View all [doc rules](https://eslint.org/docs/rules/)

You can linting tests using the commands below.

```sh
# All tests at once
npm run lint

# Every test separately
npm run scripts:lint
npm run styles:lint
```

And it's also possible to automatically fix some issues, but it won't fix everything.

```sh
# Fix all at once
npm run fix

# Every test separately
npm run scripts:fix
npm run styles:fix
```
