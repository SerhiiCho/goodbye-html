<?php

declare(strict_types=1);

use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

function tokenizeString(string $input, array $expect): void
{
    $lexer = new Lexer($input);

    foreach ($expect as $key => $expectToken) {
        $actualToken = $lexer->nextToken();

        $msg = sprintf(
            "Expected token type: \n%s\ngot: \n%s\nCase #%d",
            json_encode($expectToken, JSON_PRETTY_PRINT),
            json_encode($actualToken, JSON_PRETTY_PRINT),
            $key + 1,
        );

        expect($actualToken)->toEqual($expectToken, $msg);
    }
}

test('lexing strings', function () {
    $input = <<<HTML
    <div>
        <h2>{{ 'Hello world!' }}</h2>
        <h3>{{ "Good luck!" }}</h3>
        <h4>{{ "Good \"luck!\"" }}</h4>
    </div>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<div>\n    <h2>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::STRING, "Hello world!"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h2>\n    <h3>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::STRING, "Good luck!"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h3>\n    <h4>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::STRING, 'Good "luck!"'),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h4>\n</div>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing integers', function () {
    $input = <<<HTML
    <h1>{{ 3 }} and {{ -4 }}</h1>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h1>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::INTEGER, "3"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, " and "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::MINUS, "-"),
        new Token(TokenType::INTEGER, "4"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h1>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing floats', function () {
    $input = <<<HTML
    <h1>{{ 2.5213 }} and {{ -1.3 }}</h1>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h1>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::FLOAT, "2.5213"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, " and "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::MINUS, "-"),
        new Token(TokenType::FLOAT, "1.3"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h1>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing booleans', function () {
    $input = <<<HTML
    <h1>{{ true }}, {{ false }}, {{ !true }}</h1>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h1>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, ", "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::FALSE, "false"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, ", "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::NOT, "!"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h1>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing if expressions', function () {
    $input = <<<HTML
    {{ if true }}
        <h1>Hello world!</h1>
    {{ end }}
    HTML;

    tokenizeString($input, [
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::IF, "if"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n    <h1>Hello world!</h1>\n"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::END, "end"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing loop expressions', function () {
    $input = <<<HTML
    <ul>
        {{ loop 1, 4 }}
            <li>item</li>
        {{ end }}
    </ul>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<ul>\n    "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::LOOP, "loop"),
        new Token(TokenType::INTEGER, "1"),
        new Token(TokenType::COMMA, ","),
        new Token(TokenType::INTEGER, "4"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n        <li>item</li>\n    "),
        new Token(TokenType::OPENING_BRACES, '{{'),
        new Token(TokenType::END, "end"),
        new Token(TokenType::CLOSING_BRACES, '}}'),
        new Token(TokenType::HTML, "\n</ul>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing if else expressions', function () {
    $input = <<<HTML
    <h3>{{if true}}Main page{{else}}404{{end}}</h3>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h3>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::IF, "if"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "Main page"),
        new Token(TokenType::OPENING_BRACES, '{{'),
        new Token(TokenType::ELSE, "else"),
        new Token(TokenType::CLOSING_BRACES, '}}'),
        new Token(TokenType::HTML, "404"),
        new Token(TokenType::OPENING_BRACES, '{{'),
        new Token(TokenType::END, "end"),
        new Token(TokenType::CLOSING_BRACES, '}}'),
        new Token(TokenType::HTML, "</h3>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing ternary expression', function () {
    $input = <<<HTML
    <h3>{{ true ? 'Main page' : 'Secondary page' }}</h3>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h3>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::QUESTION_MARK, "?"),
        new Token(TokenType::STRING, "Main page"),
        new Token(TokenType::COLON, ":"),
        new Token(TokenType::STRING, "Secondary page"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h3>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing variables', function () {
    $input = <<<HTML
    <h3>My name is {{ \$my_name }}, my age is {{ \$myAge }}</h3>
    <h4>{{ \$i86 }}</h4>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h3>My name is "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::VARIABLE, "my_name"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, ", my age is "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::VARIABLE, "myAge"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h3>\n<h4>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::VARIABLE, "i86"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</h4>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing ternary expression inside html attributes', function () {
    $input = <<<HTML
    <h3 class="{{ true ? 'main-page' : 'secondary-page' }}">Hello world!</h3>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<h3 class=\""),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::QUESTION_MARK, "?"),
        new Token(TokenType::STRING, "main-page"),
        new Token(TokenType::COLON, ":"),
        new Token(TokenType::STRING, "secondary-page"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\">Hello world!</h3>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('html whitespace is not removed after lexing', function () {
    $input = <<<HTML
    <body>
        <section>
            {{ if true }}
                <h1>Hello world!</h1>
            {{ else }}
                <h1>Bye bye!</h1>
            {{ end }}
        </section>
    </body>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<body>\n    <section>\n        "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::IF, "if"),
        new Token(TokenType::TRUE, "true"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n            <h1>Hello world!</h1>\n        "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::ELSE, "else"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n            <h1>Bye bye!</h1>\n        "),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::END, "end"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n    </section>\n</body>"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing correctly without html', function () {
    $input = <<<HTML
    {{ if \$hasName }}{{ \$name }}{{ end }}
    {{ \$isAdult ? 'Adult' : 'Child' }}
    HTML;

    tokenizeString($input, [
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::IF, "if"),
        new Token(TokenType::VARIABLE, "hasName"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::VARIABLE, "name"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::END, "end"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "\n"), // <-- The only HTML token
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::VARIABLE, "isAdult"),
        new Token(TokenType::QUESTION_MARK, "?"),
        new Token(TokenType::STRING, "Adult"),
        new Token(TokenType::COLON, ":"),
        new Token(TokenType::STRING, "Child"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing illegal tokens', function () {
    $input = <<<HTML
    {{ 2.3.4 @ $ % ^ & * ( ) }}
    HTML;

    tokenizeString($input, [
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::ILLEGAL, "2.3.4"),
        new Token(TokenType::ILLEGAL, "@"),
        new Token(TokenType::ILLEGAL, "$"),
        new Token(TokenType::ILLEGAL, "%"),
        new Token(TokenType::ILLEGAL, "^"),
        new Token(TokenType::ILLEGAL, "&"),
        new Token(TokenType::ILLEGAL, "*"),
        new Token(TokenType::ILLEGAL, "("),
        new Token(TokenType::ILLEGAL, ")"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::EOF, ""),
    ]);
});

test('lexing null', function () {
    $input = <<<HTML
    <div>{{ null }}</div>
    HTML;

    tokenizeString($input, [
        new Token(TokenType::HTML, "<div>"),
        new Token(TokenType::OPENING_BRACES, "{{"),
        new Token(TokenType::NULL, "null"),
        new Token(TokenType::CLOSING_BRACES, "}}"),
        new Token(TokenType::HTML, "</div>"),
        new Token(TokenType::EOF, ""),
    ]);
});
