<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Parser\Parser;

class ParserTest extends TestCase
{
    public function testParsingVariables(): void
    {
        $input = '<div>{{ $userName }}</div>';

        $lexer = new Lexer($input);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();

        // todo: check for parser errors

        $this->assertCount(1, $program->statements, 'Program must contain 1 statement');

        /** @var VariableExpression $stmt */
        $stmt = $program->statements[0];

        $this->assertSame('userName', $stmt->value, "Variable must have value 'userName', got: '{$stmt->value}'");
        $this->assertSame('userName', $stmt->tokenLiteral(), "Variable must have token literal 'userName', got: '{$stmt->tokenLiteral()}'");
        $this->assertSame('$userName', $stmt->string(), "Variable must have string representation '\$userName', got: '{$stmt->string()}'");
    }
}
