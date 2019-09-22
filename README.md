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
Html file with 2 php variables
![php revival](https://raw.githubusercontent.com/SerhiiCho/html-parser/master/.github/before.png)

PHP string that can be passed to javascript, like generating a shortcode in WordPress
![php revival](https://raw.githubusercontent.com/SerhiiCho/html-parser/master/.github/after.png)
