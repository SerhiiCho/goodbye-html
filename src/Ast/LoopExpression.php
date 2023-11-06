<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class LoopExpression implements Expression
{
    public function __construct(
        public Token $token,
        public Expression $from,
        public Expression $to,
        public BlockStatement $body,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = sprintf("{{ loop %s, %s }}\n", $this->from->string(), $this->to->string());

        $result .= $this->body->string();

        return "{$result}{{ end }}\n";
    }
}
