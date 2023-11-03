<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class BlockStatement implements Statement
{
    /**
     * @param Statement[] $statements
     */
    public function __construct(public Token $token, public array $statements)
    {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = '';

        foreach ($this->statements as $stmt) {
            $result .= $stmt->string();
        }

        return $result;
    }
}
