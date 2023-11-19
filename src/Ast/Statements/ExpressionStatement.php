<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class ExpressionStatement implements Statement
{
    public function __construct(public Token $token, public Expression $expression)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return $this->expression->string();
    }
}
