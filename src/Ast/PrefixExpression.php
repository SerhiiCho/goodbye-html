<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class PrefixExpression implements Expression
{
    public function __construct(
        public Token $token,
        public string $operator,
        public Expression $right,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return sprintf('(%s%s)', $this->operator, $this->right->string());
    }
}
