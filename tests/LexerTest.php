<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use JsonException;
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

            try {
                $msg = sprintf(
                    "Expected token type: \n%s\ngot: \n%s\nCase #%d",
                    json_encode($expectToken, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
                    json_encode($actualToken, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
                    $key + 1,
                );
            } catch (JsonException $e) {
                $this->fail($e->getMessage());
            }

            $this->assertEquals($expectToken, $actualToken, $msg);
        }
    }

    public function testLexingStrings(): void
    {
        $input = <<<HTML
        {{ 'Hello world!' }}
        {{ "Good luck!" }}
        {{ "Good \"luck!\"" . ' Anna' }}
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::STR, "Hello world!"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::STR, "Good luck!"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::STR, 'Good "luck!"'),
            new Token(TokenType::PERIOD, "."),
            new Token(TokenType::STR, " Anna"),
            new Token(TokenType::RBRACES, "}}"),
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::INT, "3"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, " and "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::MINUS, "-"),
            new Token(TokenType::INT, "4"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</h1>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingFloats(): void
    {
        $input = <<<HTML
        <h1>{{ 2.5213 }} and {{ -1.3 }}</h1>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<h1>"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::FLOAT, "2.5213"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, " and "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::MINUS, "-"),
            new Token(TokenType::FLOAT, "1.3"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</h1>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingBooleans(): void
    {
        $input = <<<HTML
        <h1>{{ true }}, {{ false }}, {{ !true }}</h1>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<h1>"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, ", "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::FALSE, "false"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, ", "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::BANG, "!"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</h1>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingIfStatements(): void
    {
        $input = <<<HTML
        {{ if true }}
            <h1>Hello world!</h1>
        {{ end }}
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n    <h1>Hello world!</h1>\n"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingLoopStatements(): void
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::LOOP, "loop"),
            new Token(TokenType::INT, "1"),
            new Token(TokenType::COMMA, ","),
            new Token(TokenType::INT, "4"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n        <li>item</li>\n    "),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, '}}'),
            new Token(TokenType::HTML, "\n</ul>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingElseStatements(): void
    {
        $input = <<<HTML
        <h3>{{if true}}Main page{{else}}404{{end}}</h3>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<h3>"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "Main page"),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::ELSE, "else"),
            new Token(TokenType::RBRACES, '}}'),
            new Token(TokenType::HTML, "404"),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, '}}'),
            new Token(TokenType::HTML, "</h3>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingElseIfStatements(): void
    {
        $input = <<<HTML
        {{if true}}1{{else if 2}}2{{elseif 3}}3{{end}}
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "1"),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::ELSEIF, "elseif"),
            new Token(TokenType::INT, "2"),
            new Token(TokenType::RBRACES, '}}'),
            new Token(TokenType::HTML, "2"),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::ELSEIF, "elseif"),
            new Token(TokenType::INT, "3"),
            new Token(TokenType::RBRACES, '}}'),
            new Token(TokenType::HTML, "3"),
            new Token(TokenType::LBRACES, '{{'),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, '}}'),
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::QUESTION, "?"),
            new Token(TokenType::STR, "Main page"),
            new Token(TokenType::COLON, ":"),
            new Token(TokenType::STR, "Secondary page"),
            new Token(TokenType::RBRACES, "}}"),
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::VAR, "my_name"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, ", my age is "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::VAR, "myAge"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</h3>\n<h4>"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::VAR, "i86"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</h4>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingTernaryExpressionInsideHtmlAttributes(): void
    {
        $input = <<<HTML
        <h3 class="{{ true ? 'main-page' : 'secondary-page' }}">Hello world!</h3>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<h3 class=\""),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::QUESTION, "?"),
            new Token(TokenType::STR, "main-page"),
            new Token(TokenType::COLON, ":"),
            new Token(TokenType::STR, "secondary-page"),
            new Token(TokenType::RBRACES, "}}"),
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::TRUE, "true"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n            <h1>Hello world!</h1>\n        "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::ELSE, "else"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n            <h1>Bye bye!</h1>\n        "),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, "}}"),
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
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::VAR, "hasName"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::VAR, "name"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "\n"), // <-- The only HTML token
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::VAR, "isAdult"),
            new Token(TokenType::QUESTION, "?"),
            new Token(TokenType::STR, "Adult"),
            new Token(TokenType::COLON, ":"),
            new Token(TokenType::STR, "Child"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingIllegalTokens(): void
    {
        $input = <<<HTML
        {{ 2.3.4 @ $ ^ & ( ) | ~ ` }}
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::ILLEGAL, "2.3.4"),
            new Token(TokenType::ILLEGAL, "@"),
            new Token(TokenType::ILLEGAL, "$"),
            new Token(TokenType::ILLEGAL, "^"),
            new Token(TokenType::ILLEGAL, "&"),
            new Token(TokenType::ILLEGAL, "("),
            new Token(TokenType::ILLEGAL, ")"),
            new Token(TokenType::ILLEGAL, "|"),
            new Token(TokenType::ILLEGAL, "~"),
            new Token(TokenType::ILLEGAL, "`"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingNull(): void
    {
        $input = <<<HTML
        <div>{{ null }}</div>
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::HTML, "<div>"),
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::NULL, "null"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::HTML, "</div>"),
            new Token(TokenType::EOF, ""),
        ]);
    }

    public function testLexingInfixExpressions(): void
    {
        $input = <<<HTML
        {{ 4 + 5 - 2 * 3 / 4 % 2 = 5 }}
        HTML;

        $this->tokenizeString($input, [
            new Token(TokenType::LBRACES, "{{"),
            new Token(TokenType::INT, "4"),
            new Token(TokenType::PLUS, "+"),
            new Token(TokenType::INT, "5"),
            new Token(TokenType::MINUS, "-"),
            new Token(TokenType::INT, "2"),
            new Token(TokenType::ASTERISK, "*"),
            new Token(TokenType::INT, "3"),
            new Token(TokenType::SLASH, "/"),
            new Token(TokenType::INT, "4"),
            new Token(TokenType::MODULO, "%"),
            new Token(TokenType::INT, "2"),
            new Token(TokenType::ASSIGN, "="),
            new Token(TokenType::INT, "5"),
            new Token(TokenType::RBRACES, "}}"),
            new Token(TokenType::EOF, ""),
        ]);
    }
}
