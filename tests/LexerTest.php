<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class LexerTest extends TestCase
{
    public function testLexingStrings(): void
    {
        $input = <<<HTML
        <div>
            <h2>{{ 'Hello world!' }}</h2>
            <h3>{{ "Good luck!" }}</h3>
        </div>
        HTML;

        $expect = [
            new Token(TokenType::HTML, "<div>\n    <h2>"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::STRING, "Hello world!"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</h2>\n    <h3>"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::STRING, "Good luck!"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</h3>\n</div>"),
            new Token(TokenType::EOF, ""),
        ];

        $this->tokenizeString($input, $expect);
    }

    public function testLexingIntegers(): void
    {
        $input = <<<HTML
        <h1>We have {{ 3 }} computers</h1>
        HTML;

        $expect = [
            new Token(TokenType::HTML, "<h1>We have "),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::INTEGER, "3"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, " computers</h1>"),
            new Token(TokenType::EOF, ""),
        ];

        $this->tokenizeString($input, $expect);
    }

    public function testLexingBooleans(): void
    {
        $input = <<<HTML
        <h1>{{ true }} and {{ false }}</h1>
        HTML;

        $expect = [
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
        ];

        $this->tokenizeString($input, $expect);
    }

    public function testLexingIfExpressions(): void
    {
        $input = <<<HTML
        {{ if true }}
            <h1>Hello world!</h1>
        {{ end }}
        HTML;

        $expect = [
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "\n    <h1>Hello world!</h1>\n"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::EOF, ""),
        ];

        $this->tokenizeString($input, $expect);
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

        $expect = [
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
        ];

        $this->tokenizeString($input, $expect);
    }

    private function tokenizeString(string $input, array $expect): void
    {
        $lexer = new Lexer($input);

        foreach ($expect as $expectToken) {
            $actualToken = $lexer->nextToken();

            $msg = sprintf(
                "Expected token type: \n%s\ngot: \n%s",
                json_encode($expectToken, JSON_PRETTY_PRINT),
                json_encode($actualToken, JSON_PRETTY_PRINT),
            );

            $this->assertEquals($expectToken, $actualToken, $msg);
        }
    }
}
