<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

final readonly class Token
{
    public function __construct(public TokenType $type, public string $literal)
    {
    }

    public static function illegal(string $token): self
    {
        return new self(TokenType::ILLEGAL, $token);
    }
}
