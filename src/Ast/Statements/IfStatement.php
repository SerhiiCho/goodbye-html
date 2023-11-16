<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class IfStatement implements Statement
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

        $result .= $this->consequence->string();

        if ($this->alternative) {
            $result .= "{{ else }}\n";

            $result .= $this->alternative->string();
        }

        return "{$result}{{ end }}\n";
    }
}
