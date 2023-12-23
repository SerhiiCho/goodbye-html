# Release Notes

----

## v2.9.0 (2023-12-23)

- Simplified Lexer code
- Add support for grouped expressions. Now you can use them like this: `{{ (1 + 2) * 3 }}`

----

## v2.8.0 (2023-11-22)

- Added support for comparison operators like `==`, `===`, `!==`, `!=`, `<`, `>`, `<=`, `>=`. Now you can use them like this: `{{ if 1 == 1 }}`, `{{ if 1 === 1 }}`, `{{ if 1 !== 1 }}`, `{{ if 1 != 1 }}`, `{{ if 1 < 1 }}`, `{{ if 1 > 1 }}`, `{{ if 1 <= 1 }}`, `{{ if 1 >= 1 }}`
- Improved error handling for operators

----

## v2.7.0 (2023-11-22)

- Added more tests to make sure that everything works as expected
- Added more info to the `README.md` file
- Added added assign statement to the BNF grammar
- Added a third parameter to a `Parser.php` which excepts `ParserOption` ENUM

----

## v2.6.0 (2023-11-21)

- Added `elseif` statements to a BNF grammar
- Added `.gitattributes` file to ignore HTML files in `tests/files` directory
- Updated code to level 9 of the PHP Stan static analysis tool
- Fixed a typo in the change log file
- Added variable declaration statement support. Now you can declare variables like this: `{{ $name = 'Anna' }}`. Variable declaration is a statement, and must be surrounded with curly braces
- 🐛 Bug fix, `$index` variable was accessible outside of the loop. Now, it will throw an error that variable $index is undefined.

----

## v2.5.0 (2023-11-19)

- Added support for `elseif (<expression>)` and `else if (<expression>)` statements like we have in PHP. You can use them like this: `{{ if true }}<h1>True</h1>{{ elseif false }}<h1>False</h1>{{ else }}<h1>Something else</h1>{{ end }}`
- Added **PHP Stan** static analysis tool
- Added **CS Fixer** code style fixer
- 🐛 Bug fixes in the `Parser.php` class related to readonly properties being set later in the code
- Improved error handling
- Changed tests back to PHPUnit from Pest, because Pest is kinda sucks and doesn't work as I want. It lacks of error descriptive error messages when tests fail. It's hard to debug. So, I decided to go back to PHPUnit
- 🐛 Bug fix of the error that was happening when you condition of the if statement was false
- Installed `CS Fixer` to the project

----

## v2.4.0 (2023-11-17)

- Added support for math expressions: add (+), subtract (-), multiply (*), divide (/), modulo (%). Now you can use it like this: `{{ 1 + 2 }}`, `{{ 1 - 2 }}`, `{{ 1 * 2 }}`, `{{ 1 / 2 }}`, `{{ 1 % 2 }}`
- Added BNF (Backus-Naur Form) grammar
- Changed `if` and `loop` expression to statements
- Refactored and restructured AST nodes for better readability

----

## v2.3.0 (2023-11-14)

- Removed `final` keyword from all the classes to make it easier to extend the package
- Introduced Pratt Parsing
- Replaced PHPUnit with Pest testing framework
- 🐛 Fixed bug with wrong ternary expression precedence. Negation operator (!) was negating the whole ternary expression instead of the boolean part of it. Now, the negation operator (!) will negate only the boolean part of the ternary expression. Before: `(!(true ? 1 : 0))`. After: `((!true) ? 1 : 0)`.
- Added concatenation for strings. Now you can use it like this: `{{ 'Serhii ' . ' Cho' }}`

----

## v2.2.0 (2023-11-12)

- Added support for `null` type support. Now you can use `null` like this: `{{ if null }}`
- Added negation operator (`!`). Now you can use it like this: `{{ if !true }}` or `{{ if !false }}`
- Improved code readability to refactoring the `Lexer` class

----

## v2.1.0 (2023-11-12)

- Changed the first parameter of the `Parser` class. Now, it accepts absolute path to the template file or a string with template content. Before, it was accepting only the path
- Improved code readability to refactoring the `Parser` class
- Added support for `floats`. Now you can use `floats` like this: `{{ if 1.2 }}`
- 🐛 Bug fix, when you pass a relative path as a first argument to the `Parser` class. Now, it will throw an exception with a descriptive message

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
- Connected to GitHub actions

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
