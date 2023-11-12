[<< Go back to home](https://github.com/SerhiiCho/goodbye-html/blob/master/README.md)

# Release Notes

----

## v2.1.0 (2023-11-12)

- Changed the first parameter of the `Parser` class. Now, it accepts absolute path to the template file or a string with template content. Before, it was accepting only the path
- Added backslash before each PHP native function calls
- Improved code readability to refactoring the `Parser` class

----

## v2.0 (2023-11-11)

- Rewritten the whole package to proper Lexical Analyzer, Parser and Evaluator. Now you can do that you couldn't do before. Like using nested loops, if statements, ternary operators and so on.
- Rewritten the whole documentation
- Rewritten the tests
- Dropped support for PHP versions below 8.2. Now, the minimum required PHP version is 8.2
- Added BOOLEAN type support for the parser. Now you can use `true` and `false` like this: `{{ if true }}` or `{{ if false }}
- Added STRING type support for the parser. Now you can use strings like this: `{{ 'some string' }}` or `{{ if "some string" }}`
- Added quote escaping for strings. Now you can use strings like this: `{{ 'some \'string\'' }}` or `{{ if "some \"string\"" }}`
- Added support for PHP 8.3

----

## v1.6.3 (2023-10-13)

- Added `CHANGELOG.md` file to the project

----

## v1.6.2 (2023-09-07)

- Installed Pint
- Added Pint analyzer
- Added support for PHP 8.2

----

## v1.6.1 (2021-04-12)

- Small fixes

----

## v1.6 (2020-11-20)

- Added support for loops
- Fixed various bugs
- Added more tests

----

## v1.5 (2020-11-17)

- Added support for ternary operator
- Refactored code
- Added more tests
- Added more info to README.md
- Added LISCENCE.md

----

## v1.4.1 (2020-02-14)

- Added support for if/else statements

----

## v1.4 (2019-10-22)

- Added support for php7.1 in composer.json
- Connected to github actions

----

## v1.3 (2019-09-24)

- Added more tests
- Refactored the main Parser class
- Added comments
- Added information to documentation

----

##  v1.1 (2019-09-24)

- First release with if statements supported

----

##  v1.0 (2019-09-24)

- First ever version

----