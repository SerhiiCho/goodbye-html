<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use PHPUnit\Framework\Attributes\DataProvider;
use Serhii\GoodbyeHtml\Ast\Expressions\InfixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\Ast\Literals\BooleanLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\NullLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\StringLiteral;
use Serhii\GoodbyeHtml\Ast\Statements\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\Statements\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\Statements\IfStatement;
use Serhii\GoodbyeHtml\Ast\Statements\LoopStatement;
use Serhii\GoodbyeHtml\Ast\Statements\Program;
use Serhii\GoodbyeHtml\Ast\Statements\Statement;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Exceptions\CoreParserException;
use Serhii\GoodbyeHtml\Lexer\Lexer;

class CoreParserTest extends TestCase
{
    public function testParsingVariables(): void
    {
        $input = '{{ $userName }}';

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var VariableExpression $var */
        $var = $stmt->expression;

        self::testVariable($var, 'userName');
        $this->assertSame('userName', $var->tokenLiteral(), "Variable must have token literal 'userName', got: '{$var->tokenLiteral()}'");
        $this->assertSame('$userName', $var->string(), "Variable must have string representation '\$userName', got: '{$var->string()}'");
    }

    public function testParsingHtml(): void
    {
        $input = '<div class="nice"></div>';

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        $this->assertSame('<div class="nice"></div>', $stmt->string());
    }

    #[DataProvider('providerForTestBooleanLiterals')]
    public function testBooleanLiterals(string $input, string $expect): void
    {
        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var BooleanLiteral $prefix */
        $prefix = $stmt->expression;

        $this->assertInstanceOf(BooleanLiteral::class, $prefix);
        $this->assertSame($expect, $prefix->string());
    }

    public static function providerForTestBooleanLiterals(): array
    {
        return [
            ['{{ true }}', 'true'],
            ['{{ false }}', 'false'],
        ];
    }

    public function testParsingIfStatement(): void
    {
        $input = <<<HTML
        {{ if true }}
            <h1>I'm not a pro, but it's only a matter of time</h1>
        {{ end }}
        HTML;

        /** @var IfStatement $if */
        $if = $this->createProgram($input)->statements[0];

        $this->assertSame("\n    <h1>I'm not a pro, but it's only a matter of time</h1>\n", $if->consequence->string());
        $this->assertSame('true', $if->condition->string());
        $this->assertNull($if->alternative);
    }

    #[DataProvider('providerForTestParsingInfixExpressions')]
    public function testParsingInfixExpressions(string $input, mixed $left, string $operator, mixed $right): void
    {
        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var InfixExpression $infix */
        $infix = $stmt->expression;

        $this->assertInstanceOf(InfixExpression::class, $infix);
        $this->assertSame($operator, $infix->operator);
    }

    public static function providerForTestParsingInfixExpressions(): array
    {
        return [
            ['{{ 5 + 3 }}', 5, '+', 3],
            ['{{ 123 - 23 }}', 123, '-', 23],
            ['{{ 46 * 7 }}', 46, '*', 7],
            ['{{ 89 / 1 }}', 89, '/', 1],
            ['{{ 22 % 2 }}', 22, '%', 2],
            ['{{ "nice" . "cool" }}', "nice", '.', "cool"],
        ];
    }

    public function testParsingNestedIfStatement(): void
    {
        $input = <<<HTML
        {{ if \$uses_php }}You are a cool{{ if \$male }}guy{{ end }}{{ end }}
        HTML;

        /** @var IfStatement $if */
        $if = $this->createProgram($input)->statements[0];

        self::testVariable($if->condition, 'uses_php');

        $this->assertCount(2, $if->consequence->statements, 'Consequence must contain 2 statements');
        $this->assertInstanceOf(HtmlStatement::class, $if->consequence->statements[0]);
        $this->assertInstanceOf(IfStatement::class, $if->consequence->statements[1]);
        $this->assertNull($if->alternative);

        /** @var IfStatement $if */
        $if = $if->consequence->statements[1];

        $this->assertCount(1, $if->consequence->statements, 'Consequence must contain 1 statement');
        $this->assertInstanceOf(HtmlStatement::class, $if->consequence->statements[0]);
        $this->assertSame('guy', $if->consequence->statements[0]->string());
        $this->assertNull($if->alternative);

        self::testVariable($if->condition, 'male');
    }

    public function testParsingElseStatement(): void
    {
        $input = <<<HTML
        {{ if \$underAge }}<span>You are too young to be here</span>{{ else }}<span>You can drink beer</span>{{ end }}
        HTML;

        /** @var IfStatement $if */
        $if = $this->createProgram($input)->statements[0];

        self::testVariable($if->condition, 'underAge');

        $this->assertSame("<span>You are too young to be here</span>", $if->consequence->string());
        $this->assertSame("<span>You can drink beer</span>", $if->alternative->string());
    }

    public function testParsingIntegerLiteral(): void
    {
        $input = '{{ 5 }}';

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        $this->testInteger($stmt->expression, 5);
    }

    public function testParsingFloatLiteral(): void
    {
        $input = '{{ 1.40123 }}';

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        $this->testFloat($stmt->expression, 1.40123);
    }

    public function testParsingLoopStatement(): void
    {
        $input = <<<HTML
        {{ loop \$fr, 5 }}<li><a href="#">Link - {{ \$index }}</a></li>{{ end }}
        HTML;

        /** @var LoopStatement $loop */
        $loop = $this->createProgram($input)->statements[0];

        $this->testVariable($loop->from, 'fr');
        $this->testInteger($loop->to, 5);

        $this->assertCount(3, $loop->body->statements, 'Loop body must contain 3 statements');

        /** @var array<int,HtmlStatement|ExpressionStatement> $stmts */
        $stmts = $loop->body->statements;

        $this->testVariable($stmts[1]->expression, 'index');

        $this->assertSame('<li><a href="#">Link - ', $stmts[0]->string());
        $this->assertSame("</a></li>", $stmts[2]->string());
    }

    public function testParsingStrings(): void
    {
        $input = "{{ 'hello' }}";

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var StringLiteral $var */
        $str = $stmt->expression;

        $this->testString($str, 'hello');
    }

    public function testParsingTernaryExpression(): void
    {
        $input = "{{ \$hasContainer ? 'container' : '' }}";

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var TernaryExpression $ternary */
        $ternary = $stmt->expression;

        $this->assertInstanceOf(TernaryExpression::class, $ternary);

        $this->testVariable($ternary->condition, 'hasContainer');
        $this->testString($ternary->consequence, 'container');
        $this->testString($ternary->alternative, '');
    }

    #[DataProvider('providerForTestPrefixExpressions')]
    public function testPrefixExpressions(string $input, string $operator, $expect): void
    {
        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var PrefixExpression $prefix */
        $prefix = $stmt->expression;

        $this->assertInstanceOf(PrefixExpression::class, $prefix);
        $this->assertSame($operator, $prefix->operator);
    }

    public static function providerForTestPrefixExpressions(): array
    {
        return [
            ['{{ -4 }}', '-', 4],
            ['{{ -284 }}', '-', 284],
            ['{{ !true }}', '!', false],
            ['{{ !false }}', '!', true],
        ];
    }

    /**
     * @param Statement[] $stmt
     */
    private function assertOneStatement(CoreParser $parser, array $stmt): void
    {
        $this->assertCount(1, $stmt, "Program must contain 1 statements");
    }

    private static function testVariable($var, string $val): void
    {
        self::assertInstanceOf(VariableExpression::class, $var);
        self::assertSame($val, $var->value, "Variable must have value '{$val}', got: '{$var->value}'");
    }

    private static function testString($str, string $val): void
    {
        self::assertInstanceOf(StringLiteral::class, $str);
        self::assertSame($val, $str->value, "String must have value '{$val}', got: '{$str->value}'");
    }

    private static function testInteger($int, $val): void
    {
        self::assertInstanceOf(IntegerLiteral::class, $int);
        self::assertSame($val, $int->value, "Integer must have value '{$val}', got: '{$int->value}'");
    }

    private static function testFloat($float, $val): void
    {
        self::assertInstanceOf(FloatLiteral::class, $float);
        self::assertSame($val, $float->value, "Float must have value '{$val}', got: '{$float->value}'");
    }

    public function testParsingNull(): void
    {
        $input = '{{ null }}';

        /** @var ExpressionStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        /** @var NullLiteral $null */
        $null = $stmt->expression;

        self::assertInstanceOf(NullLiteral::class, $null);
        self::assertSame('null', $null->token->literal);
    }

    #[DataProvider('providerForTestOperatorPrecedenceParsing')]
    public function testOperatorPrecedenceParsing(string $input, string $expect): void
    {
        $actual = $this->createProgram($input)->string();

        $this->assertSame($expect, $actual, "Expected '{$expect}', got '{$actual}'");
    }

    public static function providerForTestOperatorPrecedenceParsing(): array
    {
        return [
            ['{{ !-1 }}', '(!(-1))'],
            ['{{ !true ? 1 : 2 }}', '((!true) ? 1 : 2)'],
            ['{{ !true ? 1 : true ? 3 : 5 }}', '((!true) ? 1 : (true ? 3 : 5))'],
        ];
    }

    private function createProgram(string $input): Program
    {
        $lexer = new Lexer($input);
        $parser = new CoreParser($lexer);

        try {
            $program = $parser->parseProgram();
        } catch (CoreParserException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertOneStatement($parser, $program->statements);

        return $program;
    }
}
