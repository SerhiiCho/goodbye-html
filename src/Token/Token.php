<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

final readonly class Token
{
    public function __construct(public TokenType $type, public string $literal)
    {
    }
}
