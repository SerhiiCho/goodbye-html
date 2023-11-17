<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class LoopStatement implements Statement
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
