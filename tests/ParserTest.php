<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfExpression;
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

    public function testParsingNestedIfStatement(): void
    {
        $input = <<<HTML
        {{ if \$uses_php }}
            You are a cool {{ if \$male }}guy{{ end }}
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

        self::testVariable($if->condition, 'uses_php');

        $this->assertCount(2, $if->consequence->statements, 'Consequence must contain 2 statements');
        $this->assertInstanceOf(HtmlStatement::class, $if->consequence->statements[0]);
        $this->assertInstanceOf(ExpressionStatement::class, $if->consequence->statements[1]);

        /** @var IfExpression $if */
        $if = $if->consequence->statements[1]->expression;

        $this->assertCount(1, $if->consequence->statements, 'Consequence must contain 1 statement');
        $this->assertInstanceOf(HtmlStatement::class, $if->consequence->statements[0]);
        $this->assertSame('guy', $if->consequence->statements[0]->string());

        self::testVariable($if->condition, 'male');
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

        $this->assertSame("<span>You are too young to be here</span>\n", $if->consequence->string());
        $this->assertSame('$underAge', $if->condition->string());
        $this->assertSame("<span>You can drink beer</span>\n", $if->alternative->string());
    }

    /**
     * @param ExpressionStatement[] $stmt
     */
    private function checkForErrors(Parser $parser, array $stmt, int $statements): void
    {
        $errors = $parser->errors();

        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }

        $this->assertCount($statements, $stmt, "Program must contain {$statements} statements");
    }

    private static function testVariable($var, string $val): void
    {
        self::assertInstanceOf(VariableExpression::class, $var);
        self::assertSame($val, $var->value);
    }
}
