# Release Notes

----

## v2.5.0 (2023-11-18)

- Added support for `elseif (<expression>)` and `else if (<expression>)` statements like we have in PHP. You can use them like this: `{{ if true }}<h1>True</h1>{{ elseif false }}<h1>False</h1>{{ else }}<h1>Something else</h1>{{ endif }}`
- Added **PHP Stan** static analysis tool
- Added **CS Fixer** code style fixer
- Bug fixes in the `Parser.php` class related to readonly properties being set later in the code
- Improved error handling

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
- Fixed bug with wrong ternary expression precedence. Negation operator (!) was negating the whole ternary expression instead of the boolean part of it. Now, the negation operator (!) will negate only the boolean part of the ternary expression. Before: `(!(true ? 1 : 0))`. After: `((!true) ? 1 : 0)`.
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
- Bug fix, when you pass a relative path as a first argument to the `Parser` class. Now, it will throw an exception with a descriptive message

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