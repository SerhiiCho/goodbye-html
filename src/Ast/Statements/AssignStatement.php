<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class AssignStatement implements Statement
{
    public function __construct(
        public Token $token,
        public VariableExpression $variable,
        public Expression $value,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return sprintf("{{ %s = %s }}", $this->variable->string(), $this->value->string());
    }
}
