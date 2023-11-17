<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Expressions;

use Serhii\GoodbyeHtml\Token\Token;

readonly class VariableExpression implements Expression
{
    public function __construct(public Token $token, public string $value)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return '$' . $this->value;
    }
}
