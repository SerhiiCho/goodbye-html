<h2 align="center">Goodbye HTML parser</h2>

<p align="center">
    <a href="https://travis-ci.org/serhii/goodbye-html"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/serhii/goodbye-html"><img src="https://poser.pugx.org/serhii/goodbye-html/v/stable.svg" alt="Latest Stable Version"></a>
</p>

## About
Very simple package for separating php code from html. It allows you to pass php **variables** in html files, and then receiving php string with clean HTML. It was mostly created for WordPress plugins and themes development. In cases if you need to create a shortcode, and want to keep HTML separate from PHP file, this package is a perfect solution.

## Example
All you need is to create a new instance of Parser class and pass the path to html file as the first constructor argument, and associative array with variable name as a key and value of the variable as the value of the array.

```php
$variables = [
    'title' => 'Title of the document',
    'is_true' => true,
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
<body>
    {{ if $is_true }}
        Some text is here
    {{ end }}
</body>
</html>
```

Parsed HTML to a PHP string
```text
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title of the document</title>
</head>
<body>
    Some text is here
</body>
</html>
```

## Allowed PHP syntax in html file

##### Variable
```html
<div>{{ $variable }}</div>
```

##### Variable in if statements
```html
{{ if $is_true }}
    <h1>This will be visible</h1>
{{ end }}
```

```html
<h1 class="{{if $is_true}}container{{end}}">
    This package is awesome
</h1>
```

## Getting started
```bash
$ composer require serhii/goodbye-html
```
