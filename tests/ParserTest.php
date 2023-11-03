<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Ast\HtmlStatement;
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

        $this->checkForErrors($parser, $program->statements, 3);

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[1];

        /** @var VariableExpression $var */
        $var = $stmt->expression;

        $this->assertSame('userName', $var->value, "Variable must have value 'userName', got: '{$var->value}'");
        $this->assertSame('userName', $var->tokenLiteral(), "Variable must have token literal 'userName', got: '{$var->tokenLiteral()}'");
        $this->assertSame('$userName', $var->string(), "Variable must have string representation '\$userName', got: '{$var->string()}'");
    }

    public function testParsingHtml(): void
    {
        $input = '<div class="nice">{{ $age }}</div>';

        $lexer = new Lexer($input);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();

        $this->checkForErrors($parser, $program->statements, 3);

        /** @var HtmlStatement $stmt1 */
        $stmt1 = $program->statements[0];

        /** @var HtmlStatement $stmt2 */
        $stmt2 = $program->statements[2];

        $this->assertSame('<div class="nice">', $stmt1->string());
        $this->assertSame('</div>', $stmt2->string());
    }

    public function testParsingIfStatement(): void
    {
        $input = <<<HTML
        {{ if \$uses_php }}
            <h1>I'm not a pro but it's only a matter of time</h1>
        {{ end }}
        HTML;

        $lexer = new Lexer($input);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();

        $this->checkForErrors($parser, $program->statements, 1);

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];

        /** @var IfExpression */
        $if = $stmt->expression;

        $this->assertSame("<h1>I'm not a pro but it's only a matter of time</h1>\n", $if->consequence->string());
        $this->assertSame('$uses_php', $if->condition->string());
        $this->assertNull($if->alternative);
    }

    public function testParsingElseStatement(): void
    {
        $input = <<<HTML
        {{ if \$underAge }}
            <span>You are too young to be here</span>
        {{ else }}
            <span>You can drink beer</span>
        {{ end }}
        HTML;

        $lexer = new Lexer($input);
        $parser = new Parser($lexer);

        $program = $parser->parseProgram();

        $this->checkForErrors($parser, $program->statements, 1);

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];

        /** @var IfExpression */
        $if = $stmt->expression;

        $this->assertSame("<span>You are too young to be here</span>\n", $if->consequence[0]->string());
        $this->assertSame('$underAge', $if->condition->string());
        $this->assertEmpty("<span>You can drink beer</span>\n", $if->alternative[0]->string());
    }

    /**
     * @param ExpressionStatement[] $stmt
     */
    private function checkForErrors(Parser $parser, array $stmt, int $statements): void
    {
        $errors = $parser->errors();

        $this->assertCount($statements, $stmt, "Program must contain {$statements} statements");

        if (count($errors) === 0) {
            return;
        }

        $this->fail(implode("\n", $errors));
    }
}
