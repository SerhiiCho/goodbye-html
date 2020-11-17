<h2 align="center">Goodbye HTML</h2>

<p align="center">
    <a href="https://actions-badge.atrox.dev/SerhiiCho/goodbye-html/goto"><img alt="Build Status" src="https://img.shields.io/endpoint.svg?url=https%3A%2F%2Factions-badge.atrox.dev%2FSerhiiCho%2Fgoodbye-html%2Fbadge&style=flat" /></a>
    <a href="https://travis-ci.org/serhii/goodbye-html"><img src="https://travis-ci.org/SerhiiCho/goodbye-html.svg?branch=master" alt="build:passed"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/v/stable.svg" alt="Latest Stable Version"></a>
</p>

A very simple package for separating php code from HTML or any other text file. It allows you to pass php **variables** in files, and then you've got a string with the content of your file with replaced variables. It was mostly created for WordPress plugins and themes development. In cases if you need to create a shortcode, and you want to keep your HTML separate from PHP file, this package is a perfect solution.

PLEASE NOTE! This package is not parsing HTML files like other templating engines, all it does is just taking your values from an associative array that you pass to constructor, and replacing all embedded variables with those values. You cannot use || or && operators or any other php syntax. The syntax of the parser has nothing to do with php.

## Example
All you need is to create a new instance of Parser class and pass the path to html file as the first constructor argument, and associative array with the variable name as a key and value of the variable as the value of the array.

```php
$variables = [
    'title' => 'Title of the document',
    '$uses_php_3_years' => true,
    'show_container' => false,
];

$parser = new Parser('hello.html', $variables);

echo $parser->parseHtml(); // this will output HTML
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
    <h1>I'm not a pro but it's only a matter of time</h1>
</body>
</html>
```

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
    {{ if $show_title }}
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
    <h1>{{ if $is_true }}This will be visible if true{{ else }}This will be visible if false {{ end }}</h1>
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

```html
<!-- Inside html attributes -->
<section class="{{ $is_true ? 'container' : '' }}">
    <h1>Title</h1>
</section>
```

```html
<!-- With strings -->
<section class="container">
    {{ $is_true ? '<h1>Title</h1>' : '<h2>Secondary</h2>' }}
</section>
```

```html
<!-- With variables -->
<section class="container">
    {{ $has_apple ? $with_apple : $without_apple }}
</section>
```

## Getting started
```bash
$ composer require serhii/goodbye-html
```
