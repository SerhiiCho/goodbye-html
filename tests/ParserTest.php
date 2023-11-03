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

        $this->checkForErrors($parser);

        $this->assertCount(3, $program->statements, 'Program must contain 3 statements');

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[1];

        /** @var VariableExpression $var */
        $var = $stmt->expression;

        $this->assertSame('userName', $var->value, "Variable must have value 'userName', got: '{$var->value}'");
        $this->assertSame('userName', $var->tokenLiteral(), "Variable must have token literal 'userName', got: '{$var->tokenLiteral()}'");
        $this->assertSame('$userName', $var->string(), "Variable must have string representation '\$userName', got: '{$var->string()}'");
    }

    private function checkForErrors(Parser $parser): void
    {
        $errors = $parser->errors();

        if (count($errors) === 0) {
            return;
        }

        $this->fail(implode("\n", $errors));
    }
}
