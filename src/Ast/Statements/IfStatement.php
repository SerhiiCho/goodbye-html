<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast\Statements;

use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Token\Token;

readonly class IfStatement implements Statement
{
    /**
     * @param list<ElseIfStatement> $elseIfBlocks
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
        $result = sprintf("{{ if %s }}", $this->condition->string());

        $result .= $this->block->string();

        foreach ($this->elseIfBlocks as $elseIfBlock) {
            $result .= $elseIfBlock->string();
        }

        if ($this->elseBlock) {
            $result .= "{{ else }}";
            $result .= $this->elseBlock->string();
        }

        return "{$result}{{ end }}\n";
    }
}
