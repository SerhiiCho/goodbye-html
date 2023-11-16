<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Literals;

use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class BooleanLiteral implements Expression
{
    public function __construct(public Token $token, public bool $value)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
