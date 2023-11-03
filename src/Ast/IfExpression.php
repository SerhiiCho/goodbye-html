<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

use Serhii\GoodbyeHtml\Token\Token;

readonly class IfExpression implements Expression
{
    /**
     * @param Statement[] $consequence
     * @param Statement[] $alternative
     */
    public function __construct(
        public Token $token,
        public Expression $condition,
        public array $consequence,
        public array $alternative,
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = sprintf("{{ if %s }}\n", $this->condition->string());

        foreach ($this->consequence as $stmt) {
            $result .= $stmt->string();
        }

        if (count($this->alternative) > 0) {
            $result .= "{{ else }}\n";
        }

        foreach ($this->alternative as $stmt) {
            $result .= $stmt->string();
        }

        return "{$result}{{ end }}\n";
    }
}
