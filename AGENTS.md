# AGENTS.MD

Be concise when opening a pull request. No need for unnecessarily verbose text or endless checklists. The maintainers are mostly interested in special points of consideration when reviewing the code and whether the change introduces any breaking changes. 

That your code adheres to our coding standards, does not break any tests, uses proper sanitization and escaping, etc. is implied. There is no need to repeat that in text as it makes it easier to miss the important points of your pull request.

## Testing instructions

- Find the CI plan in the `.github/workflows` directory. Ensure that your code changes do not break any of the tests in the CI plan.
- Run `composer run-script test` to run the PHP test suite.
- Run `composer run-script check-syntax` to check for PHP syntax errors.
- Run `composer run-script check-codestyle` to check the PHP code style.
- Run `composer run-script static-analysis` to run static analysis on the PHP code.
- Run `npm run lint` to check the JS code style.
