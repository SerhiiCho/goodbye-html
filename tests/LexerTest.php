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
        $input = '
        <div>
            <h1>{{ $name }}</h1>
        </div>
        ';

        $tests = [
            new Token(TokenType::HTML, '<div><h1>'),
            new Token(TokenType::LEFT_BRACES, '{{'),
            new Token(TokenType::VARIABLE, 'name'),
            new Token(TokenType::RIGHT_BRACES, '}}'),
            new Token(TokenType::HTML, '</h1></div>'),
            new Token(TokenType::EOF, ''),
        ];

        $lexer = new Lexer($input);

        foreach ($tests as $test) {
            $token = $lexer->nextToken();
            $this->assertEquals($test->literal, $token->literal);
            $this->assertEquals($test->type, $token->type);
        }
    }
}
