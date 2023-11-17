<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

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
            if ($stmt instanceof HtmlStatement) {
                $result .= $stmt->string();
            } else {
                $result .= "{{ {$stmt->string()} }}";
            }
        }

        return $result;
    }
}
