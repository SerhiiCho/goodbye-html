<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class LexerTest extends TestCase
{
    private function tokenizeString(string $input, array $expect): void
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

            $this->assertEquals($expectToken, $actualToken, $msg);
        }
    }

    public function testLexingStrings(): void
    {
        $input = <<<HTML
        <div>
            <h2>{{ 'Hello world!' }}</h2>
            <h3>{{ "Good luck!" }}</h3>
            <h4>{{ "Good \"luck!\"" }}</h4>
        </div>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingIntegers(): void
    {
        $input = <<<HTML
        <h1>{{ 3 }} and {{ -4 }}</h1>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingBooleans(): void
    {
        $input = <<<HTML
        <h1>{{ true }} and {{ false }}</h1>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<h1>"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, " and "),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::FALSE, "false"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</h1>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingIfExpressions(): void
    {
        $input = <<<HTML
        {{ if true }}
            <h1>Hello world!</h1>
        {{ end }}
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingLoopExpressions(): void
    {
        $input = <<<HTML
        <ul>
            {{ loop 1, 4 }}
                <li>item</li>
            {{ end }}
        </ul>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingIfElseExpressions(): void
    {
        $input = <<<HTML
        <h3>{{if true}}Main page{{else}}404{{end}}</h3>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingTernaryExpression(): void
    {
        $input = <<<HTML
        <h3>{{ true ? 'Main page' : 'Secondary page' }}</h3>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingVariables(): void
    {
        $input = <<<HTML
        <h3>My name is {{ \$my_name }}, my age is {{ \$myAge }}</h3>
        <h4>{{ \$i86 }}</h4>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testLexingTernaryExpressionInsideHtmlAttributes()
    {
        $input = <<<HTML
        <h3 class="{{ true ? 'main-page' : 'secondary-page' }}">Hello world!</h3>
        HTML;

        $this->tokenizeString($input, [
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
    }

    public function testHtmlWhitespaceIsNotRemovedAfterLexing(): void
    {
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

        $this->tokenizeString($input, [
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
    }

    public function testLexingCorrectlyWithoutHtml(): void
    {
        $input = <<<HTML
        {{ if \$hasName }}{{ \$name }}{{ end }}
        {{ \$isAdult ? 'Adult' : 'Child' }}
        HTML;

        $this->tokenizeString($input, [
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
    }
}
