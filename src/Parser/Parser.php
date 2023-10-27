<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Parser;

use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\Statement;
use Serhii\GoodbyeHtml\Lexer\Lexer;

final readonly class Parser
{
    public function __construct(private Lexer $lexer)
    {
    }

    public function parseProgram(): Program
    {
        /** @var Statement[] $statements */
        $statements = [];

        //

        return new Program($statements);
    }

    private function parseStatement(): Statement|null
    {
        return null;
    }
}
