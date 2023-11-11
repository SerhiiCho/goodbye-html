<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class TernaryExpression implements Expression
{
    public function __construct(
        public Token $token,
        public Expression $condition,
        public Expression $consequence,
        public Expression $alternative,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $cond = $this->condition->string();
        $con = $this->consequence->string();
        $alt = $this->alternative->string();

        return "{{ {$cond} ? {$con} : {$alt} }}";
    }
}
