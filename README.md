# Html Parser

#### About
This package allows you to write php variables in html files, and then converting it to php string.

#### Example
All you need is to create a new instance of Parser class and pass the path to html file as the first constructor argument, and associative array with variable name as a key and value of the variable as the value of the array.
```php
$variables = [
    'title' => 'Title of the document',
    'body_text' => 'Hello, here is the body',
];

$parser = new Parser('hello.html', $variables);

echo $parser->parseHtml(); // this will output parsed html
```

Html file with 2 php variables before parsing
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

PHP string that can be passed to JavaScript, like generating a shortcode in WordPress, or printing it to a screen
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
