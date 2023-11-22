<p align="center">
    <a href="https://github.com/SerhiiCho/goodbye-html/actions/workflows/php.yml"><img src="https://github.com/SerhiiCho/goodbye-html/actions/workflows/php.yml/badge.svg" alt="Goodbye HTML"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://github.com/SerhiiCho/goodbye-html/blob/master/LICENSE.md"><img alt="GitHub" src="https://img.shields.io/github/license/SerhiiCho/goodbye-html"></a>
</p>

A very simple package for separating PHP logic from HTML or any other text. It allows you to insert **variables**, **if/else statements**, **loops** and **ternary operators** into any text file and dynamically get parsed content of this file.

- [📝 Release notes](https://github.com/SerhiiCho/goodbye-html/blob/main/docs/changelog.md)
- [✏️ BNF grammar](https://github.com/SerhiiCho/goodbye-html/blob/main/docs/goodbye-html.bnf)

## Supported PHP versions
- ✅ 8.2
- ✅ 8.3

## What is it for?
This package is useful when you need to separate PHP logic from HTML or any other text. For example if you need to send an email with some dynamic content, you can create a template file with HTML and insert variables, if/else statements, loops and ternary operators into it. Then you can pass this file to the parser and get parsed content of this file as a string. Then you can use this string as a content of your email.

## What is it not for?
This package is not for creating a full-featured template engine. It's just a simple parser that allows you to insert some PHP logic into any text file. It's not for creating a full-featured template engine like [Twig](https://twig.symfony.com/), [Blade](https://laravel.com/docs/8.x/blade) or [Latte](https://latte.nette.org/en/). If you need a full-featured template engine, you should use one of the mentioned above.

## What Goodbye HTML has?
- [x] Variables
    - [x] Assigning variables
    - [x] Using variables
    - [x] Printing variables
- [x] If/Else-If/Else statements
- [x] Ternary expressions
- [x] Loops
- [x] Prefix operators
    - Negation operator (!)
    - Minus operator (-)
- [x] String concatenation
- [x] Math operations
    - Addition
    - Subtraction
    - Multiplication
    - Division
    - Modulus

## Usage

```php
use Serhii\GoodbyeHtml\Parser;

$variables = [
    'title' => 'Title of the document',
    'uses_php_3_years' => true,
    'show_container' => false,
];

// Absolute file path to a text file
$file_path = __DIR__ . '/hello.html';

$parser = new Parser($file_path, $variables);

// this will parsed content of hello.html file
echo $parser->parseHtml();
```

HTML file content with 2 php variables before parsing it
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body class="{{ $show_container ? 'container' : '' }}">
    <nav>
        <ul>
            {{ loop 1, 3 }}
                <li><a href="#">Link - {{ $index }}</a></li>
            {{ end }}
        </ul>
    </nav>

    {{ if $uses_php_3_years }}
        <h1>I'm not a pro, but it's only a matter of time</h1>
    {{ end }}
</body>
</html>
```

Parsed HTML to a PHP string
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title of the document</title>
</head>
<body class="">
    <nav>
        <ul>
            
                <li><a href="#">Link - 1</a></li>
            
                <li><a href="#">Link - 2</a></li>
            
                <li><a href="#">Link - 3</a></li>
            
        </ul>
    </nav>

    
        <h1>I'm not a pro, but it's only a matter of time</h1>
    
</body>
</html>
```

### Same example but for WordPress shortcode

```php
use Serhii\GoodbyeHtml\Parser;

add_shortcode('my_shortcode', 'shortcode_callback');

function shortcode_callback() {
    $parser = new Parser(__DIR__ . '/shortcodes/main.html', [
        'title' => 'Title of the document',
        'uses_php_3_years' => true,
        'show_container' => false,
    ]);
    return $parser->parseHtml();
}
```

## Options

The instance of `Parser` class takes the third argument as a `ParserOption` enum. You can pass it to the constructor of the `Parser` class as a third argument. For now, it has only a single options:

#### `ParserOption::PARSE_TEXT`
If you pass this option, the parser, instead of getting the content of the provided file path, will parse the provided string. This option is useful when you want to parse a string instead of a file.

```php
$parser = new Parser('<div>{{ $title }}</div>', [
    'title' => 'Hello world'
], ParserOption::PARSE_TEXT);

// output: <div>Hello world</div>
```

## Supported types

Types that you can pass to the parser to include them in the `html/text` file. Note that not all PHP types are supported for know. More types will be added in next releases.

| PHP Type | Value example |
|----------|---------------|
| bool     | true          |
| string   | 'Is title'    |
| int      | 24            |
| float    | 3.1415        |
| null     | null          |

## Supported prefix operators

Prefix operators are used to change the value of the variable. For example if you have a variable `$is_smoking` and you want to check if it's false, you can use `!` prefix operator to change the value of the variable to false. Or if you have a variable `$age` and you want to make it negative, you can use `-` prefix operator to change the value of the variable to negative.

| Prefix name | Prefix value | Example | Supported types for prefix |
|-------------|--------------|---------|----------------------------|
| Not         | !            | !true   | all the types              |
| Minus       | -            | -24     | int, float                 |

## Supported infix operators

Infix operators are used to perform math operations or string concatenation. For example if you have a variable `$age` and you want to add 1 to it, you can use `+` infix operator to add 1 to the variable. Or if you have a variable `$first_name` and you want to concatenate it with `$last_name`, you can use `.` infix operator to concatenate these 2 variables.

| Operator name | Operator literal | Example            | Supported types for prefix |
|---------------|------------------|--------------------|----------------------------|
| Plus          | +                | 3 + 4              | int, float                 |
| Minus         | -                | 5 - 4              | int, float                 |
| Multiply      | *                | 3 * 4              | int, float                 |
| Divide        | /                | 6 / 3              | int, float                 |
| Modulo        | %                | 5 % 2              | int, float                 |
| Concatenate   | .                | 'Hello' . ' world' | string                     |
| Assigning     | =                | {{ $a = 5 }}       | all the types              |

## All the available syntax in html/text file

#### Variable

```html
<!-- Inside html tags -->
<div>{{ $guest_name }}</div>
```

```html
<!-- Inside attributes -->
<h2 class="{{ $styles }}">The title of the page</h2>
```

#### If statements

```html
<!-- Block syntax -->
<section>
    {{ if true }}
        <h1>PHP is awesome programming language</h1>
    {{ end }}
</section>
```

```html
<!-- Inline syntax -->
<h1 class="{{if $show_container}}container{{end}}">
    This package is cool
</h1>
```

#### If/Else statements

```html
<!-- Block syntax -->
<section>
    {{ if $likes_bread }}
        <h1>I like bread</h1>
    {{ else }}
        <h1>I don't really like bread</h1>
    {{ end }}
</section>
```

```html
<!-- Inline syntax -->
<section>
    <h1>{{ if $late }}It's late{{ else }}It's not late{{ end }}</h1>
</section>
```

#### If/Else-If/Else statements

> Similar to PHP, you can write `elseif` or `else if` in the same way.

```html
<!-- Block syntax -->
<section>
    {{ if $likes_bread }}
        <h1>I like bread</h1>
    {{ else if $likes_cake }}
        <h1>I like cake</h1>
    {{ elseif $likes_pizza }}
        <h1>I like pizza</h1>
    {{ else }}
        <h1>I don't really like anything</h1>
    {{ end }}
</section>
```

```html
<!-- Inline syntax -->
<section>
    <h1>I like {{ if $likes_bread }}bread{{ else if $likes_cake }}cake{{ else }}just water{{ end }}</h1>
</section>
```

#### Ternary operator

[Ternary operator](https://en.wikipedia.org/wiki/%3F:) is commonly referred to as the conditional operator, inline if/else. An expression `a ? b : c` evaluates to `b` if the value of `a` is true, and otherwise to `c`. One can read it aloud as "if a then b otherwise c".

```html
<!-- Inside html attributes -->
<section class="{{ $wrap ? 'container' : '' }}">
    <h1>Title</h1>
</section>
```

```html
<!-- With strings -->
<section class="container">
    {{ $show_main_title ? '<h1>Main title</h1>' : '<h2>Secondary</h2>' }}
</section>
```

```html
<!-- With variables -->
<section class="container">
    {{ $has_apple ? $with_apple : $without_apple }}
</section>
```

#### Loops

Loop takes 2 integer arguments. The first argument is from what number start looping, and the second argument is where to stop. For example if you start from 1 to 4, it's going to result 4 repeated blocks. Inside each loop you can use $index variable that is going to have a value of current iteration number.

```html
<!-- Block syntax -->
<div>
    {{ loop 0, 5 }}
        <h1>Hello world {{ $index }}</h1>
    {{ end }}
</div>
```

```html
<!-- Inline syntax -->
<div>
    <h1 class="{{ loop 1, 4 }}class-{{$index}} {{ end }}"></h1>
</div>
```

```html
<!-- With integer variables -->
<div>
    {{ loop $from, $to }}
        <h1>Hello world {{ $index }}</h1>
    {{ end }}
</div>
```

#### Assigning statements

You can assign values to variables inside your text files using curly braces. For example if you want to assign value 5 to variable `$a`, you can do it like this `{{ $a = 5 }}`. You can also use prefix operators to change the value of the variable. For example if you want to assign value false to variable `$is_smoking`, you can do it like this `{{ $is_smoking = !true }}`.

```html
<div>{{ $age = 33 }}</div>
```

## Getting started

```bash
$ composer require serhii/goodbye-html
```
