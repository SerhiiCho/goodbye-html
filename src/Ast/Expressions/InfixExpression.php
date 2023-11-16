<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Expressions;

use Serhii\GoodbyeHtml\Token\Token;

readonly class InfixExpression implements Expression
{
    public function __construct(
        public Token $token,
        public Expression $left,
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
        return sprintf(
            '(%s %s %s)',
            $this->left->string(),
            $this->operator,
            $this->right->string(),
        );
    }
}
