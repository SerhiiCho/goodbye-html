<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Ast;

final readonly class Program implements Statement
{
    /**
     * @param Statement[] $statements
     */
    public function __construct(public array $statements)
    {
    }

    public function tokenLiteral(): string
    {
        if (count($this->statements) === 0) {
            return '';
        }

        return $this->statements[0]->tokenLiteral();
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
