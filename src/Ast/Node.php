<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

interface Node
{
    /**
     * Returns token literal, like "if", "else", "end", etc.
     */
    public function tokenLiteral(): string;

    /**
     * Returns string representation of the node
     */
    public function string(): string;
}
