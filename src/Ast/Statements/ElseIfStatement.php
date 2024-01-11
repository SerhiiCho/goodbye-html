<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class ElseIfStatement implements Statement
{
    public function __construct(
        public Token $token,
        public Expression $condition,
        public BlockStatement $block,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = sprintf("{{ else if %s }}", $this->condition->string());
        return $result . $this->block->string();
    }
}
