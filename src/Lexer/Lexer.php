<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Lexer;

use Serhii\GoodbyeHtml\Token\Token;

final readonly class Lexer
{
    public function __construct(private string $input)
    {
    }

    public function nextToken(): Token
    {
        return new Token();
    }

    private function advanceChar()
    {
    }
}
