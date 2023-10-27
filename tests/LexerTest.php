<?php

declare(strict_types=1);

namespace Serhii\Tests;

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

        $expect = [
            new Token(TokenType::LEFT_BRACES, '{{'),
            new Token(TokenType::VARIABLE, 'name'),
            new Token(TokenType::RIGHT_BRACES, '}}'),
            new Token(TokenType::EOF, ''),
        ];
    }
}
