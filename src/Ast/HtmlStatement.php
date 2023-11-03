<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class HtmlStatement implements Statement
{
    public function __construct(public Token $token)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        return $this->token->literal;
    }
}
