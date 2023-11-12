<p align="center">
    <a href="https://github.com/SerhiiCho/goodbye-html/actions/workflows/php.yml"><img src="https://github.com/SerhiiCho/goodbye-html/actions/workflows/php.yml/badge.svg" alt="Goodbye HTML"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://github.com/SerhiiCho/goodbye-html/blob/master/LICENSE.md"><img alt="GitHub" src="https://img.shields.io/github/license/SerhiiCho/goodbye-html"></a>
</p>

A very simple package for separating PHP logic from HTML or any other text. It allows you to insert **variables**, **if/else statements**, **loops** and **ternary operators** into any text file and dynamically get parsed content of this file. It is useful in things like WordPress plugins and themes. If you need to create a WordPress shortcode, and you want to keep your HTML separate from PHP.

## Supported PHP versions
- ✅ 8.2
- ✅ 8.3

## Usage

```php
use Serhii\GoodbyeHtml\Parser;

$variables = [
    'title' => 'Title of the document',
    'uses_php_3_years' => true,
    'show_container' => false,
];

// Absolute file path or file content as a string
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
        <h1>I'm not a pro but it's only a matter of time</h1>
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

    
        <h1>I'm not a pro but it's only a matter of time</h1>
    
</body>
</html>
```
## Same example but for WordPress shortcode

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

## Supported types

Types that you can pass to the parser to include them in the `html/text` file. Note that not all PHP types are supported for know. More types will be added in next releases.

| PHP Type    | Value example |
| ----------- | ------------- |
| bool        | true          |
| string      | 'Is title'    |
| int         | 24            |
| float       | 3.1415        |
| null        | null          |

## Supported prefix operators

Prefix operators are used to change the value of the variable. For example if you have a variable `$is_smoking` and you want to check if it's false, you can use `!` prefix operator to change the value of the variable to false. Or if you have a variable `$age` and you want to make it negative, you can use `-` prefix operator to change the value of the variable to negative.

| Prefix name | Prefix value  | Example     | Supported types for prefix |
| ----------- | ------------- | ----------- | -------------------------- |
| NOT         | !             | !true       | all the types              |
| MINUS       | -             | -24         | int, float                 |

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

```html
<!-- Variable inside block syntax -->
<section>
    {{ if $drinks_lots_of_water }}
        <h1>{{ $water_benefits }}</h1>
    {{ end }}
</section>
```

```html
<!-- Variable inside inline syntax -->
<h1 class="{{if $is_smoking}}{{ $harm_description }}{{end}}">
    This package is cool
</h1>
```

#### If / else statements

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

```html
<!-- Variable inside block syntax -->
<section>
    {{ if $animal }}
        <h1>{{ $tiger_var }}</h1>
    {{ else }}
        <h1>{{ $fish_var }}</h1>
    {{ end }}
</section>
```

```html
<!-- Variable inside inline syntax -->
<section>
    <h1>{{ if $is_cat }}{{ $cat_var }}{{ else }}{{ $dog_var }}{{ end }}</h1>
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

Loop takes 2 integer arguments. The first argument is from what number start looping, and the second argument is where to stop. For example if you start from 1 to 4, it's gonna result 4 repeated blocks. Inside each loop you can use $index variable that is going to have a value of current iteration number.

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

## Getting started
```bash
$ composer require serhii/goodbye-html
```
