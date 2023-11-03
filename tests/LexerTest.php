<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class LexerTest extends TestCase
{
    public function testLexer(): void
    {
        $input = <<<HTML
        <div class="{{ \$show20 ? 'container' : '' }}">
        <h1 {{ \$classes }}>{{ \$heading }}</h1>

        <ul>
        {{ loop 1, \$to }}
            <li>{{ \$index }}</li>
        {{ end }}
        </ul>

        {{ if \$is_old }}
            <h1>I'm not a pro but it's only a matter of time</h1>
        {{ end }}
        </div>

        {{ if \$likes_bread }}
            <h1>I like bread</h1>
        {{ else }}
            <h1>I don't really like bread</h1>
        {{ end }}

        <h1>{{ if \$is_cat }}{{ \$cat_var }}{{ else }}{{ \$dog_var }}{{ end }}</h1>

        {{ if \$uses_php }}
            You are a cool {{ if \$male }}guy{{ end }}
        {{ end }}
        HTML;

        $tests = [
            new Token(TokenType::HTML, "<div class=\""),

            // Coalescing operator
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, "show20"),
            new Token(TokenType::QUESTION_MARK, "?"),
            new Token(TokenType::STRING, "container"),
            new Token(TokenType::COLON, ":"),
            new Token(TokenType::STRING, ""),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            // End coalescing operator

            // Variables
            new Token(TokenType::HTML, "\">\n<h1 "),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, "classes"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, ">"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, "heading"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</h1>\n\n<ul>\n"),
            // End variables

            // Loop statement
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::LOOP, "loop"),
            new Token(TokenType::INTEGER, "1"),
            new Token(TokenType::COMMA, ","),
            new Token(TokenType::VARIABLE, "to"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "<li>"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, "index"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</li>\n"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            // End loop statement

            new Token(TokenType::HTML, "</ul>\n\n"),

            // If statement
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::VARIABLE, "is_old"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "<h1>I'm not a pro but it's only a matter of time</h1>\n"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            // End if statement

            new Token(TokenType::HTML, "</div>\n\n"),

            // If / Else statement
            new Token(TokenType::OPENING_BRACES, '{{'),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::VARIABLE, "likes_bread"),
            new Token(TokenType::CLOSING_BRACES, '}}'),
            new Token(TokenType::HTML, "<h1>I like bread</h1>\n"),
            new Token(TokenType::OPENING_BRACES, '{{'),
            new Token(TokenType::ELSE, "else"),
            new Token(TokenType::CLOSING_BRACES, '}}'),
            new Token(TokenType::HTML, "<h1>I don't really like bread</h1>\n"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            // End If / Else statement

            // Inline If / Else / End statement
            new Token(TokenType::HTML, "<h1>"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::IF, "if"),
            new Token(TokenType::VARIABLE, 'is_cat'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, 'cat_var'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::ELSE, 'else'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::VARIABLE, 'dog_var'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, "</h1>\n\n"),
            // End inline If / Else / End statement

            // Nested If statement
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::IF, 'if'),
            new Token(TokenType::VARIABLE, 'uses_php'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, 'You are a cool '),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::IF, 'if'),
            new Token(TokenType::VARIABLE, 'male'),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::HTML, 'guy'),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            new Token(TokenType::OPENING_BRACES, "{{"),
            new Token(TokenType::END, "end"),
            new Token(TokenType::CLOSING_BRACES, "}}"),
            // End nested If statement

            new Token(TokenType::EOF, 'EOF'),
        ];

        $lexer = new Lexer($input);

        foreach ($tests as $test) {
            $token = $lexer->nextToken();
            $this->assertEquals($test->type, $token->type, "Test for: {$test->literal}");
            $this->assertEquals($test->literal, $token->literal, "Test for token type: {$token->type->value}");
        }
    }
}
