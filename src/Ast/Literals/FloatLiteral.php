<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Literals;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class FloatLiteral implements Expression
{
    public function __construct(public Token $token, public float $value)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return (string) $this->value;
    }
}
