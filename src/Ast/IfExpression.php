<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class IfExpression implements Expression
{
    public function __construct(
        public Token $token,
        public Expression $condition,
        public BlockStatement $consequence,
        public ?BlockStatement $alternative,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = sprintf("{{ if %s }}\n", $this->condition->string());

        foreach ($this->consequence->statements as $stmt) {
            $result .= $stmt->string();
        }

        if ($this->alternative) {
            $result .= "{{ else }}\n";

            foreach ($this->alternative->statements as $stmt) {
                $result .= $stmt->string();
            }
        }

        return "{$result}{{ end }}\n";
    }
}
