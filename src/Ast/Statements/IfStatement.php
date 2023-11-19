<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class IfStatement implements Statement
{
    /**
     * @param array<int,IfStatement> $elseIfBlocks
     */
    public function __construct(
        public Token $token,
        public Expression $condition,
        public BlockStatement $block,
        public ?BlockStatement $elseBlock = null,
        public array $elseIfBlocks = [],
    ) {
    }

    public function tokenLiteral(): string
    {
        return $this->token->literal;
    }

    public function string(): string
    {
        $result = sprintf("{{ if %s }}\n", $this->condition->string());

        $result .= $this->block->string();

        if ($this->elseBlock) {
            $result .= "{{ else }}\n";

            $result .= $this->elseBlock->string();
        }

        return "{$result}{{ end }}\n";
    }
}
