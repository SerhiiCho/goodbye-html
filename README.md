## About
This package allows you to write php variables in html files, and then converting it to php string. It can be useful when you need to pass html to JavaScript, like generating a shortcode in WordPress, or printing the output to a screen.

## Example
All you need is to create a new instance of Parser class and pass the path to html file as the first constructor argument, and associative array with variable name as a key and value of the variable as the value of the array.

```php
$variables = [
    'title' => 'Title of the document',
    'body_text' => 'Hello, here is the body',
];

$parser = new Parser('hello.html', $variables);

echo $parser->parseHtml(); // this will output parsed html
```

HTML file content with 2 php variables before parsing it
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body>
    {{ $body_text }}
</body>
</html>
```

Parsed HTML to a PHP string
```text
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title of the document</title>
</head>
<body>
    Hello, here is the body
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

` Note that this is a very simple parser, it can only convert variables and variables wrapped in if statement.`

## Getting started
```bash
$ composer require serhii/html-parser
```