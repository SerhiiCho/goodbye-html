<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Expressions;

use Serhii\GoodbyeHtml\Token\Token;

readonly class PrefixExpression implements Expression
{
    /**
     * @param non-empty-string $operator
     */
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

    /**
     * @return non-empty-string
     */
    public function string(): string
    {
        return sprintf('(%s%s)', $this->operator, $this->right->string());
    }
}
