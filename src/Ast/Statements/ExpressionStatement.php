<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class ExpressionStatement implements Statement
{
    public function __construct(public Token $token, public Expression|null $expression)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        if (!$this->expression) {
            return '';
        }

        return $this->expression->string();
    }
}
