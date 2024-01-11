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
use Serhii\GoodbyeHtml\Ast\Statements\AssignStatement;
use Serhii\GoodbyeHtml\Ast\Statements\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\Statements\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\Statements\IfStatement;
use Serhii\GoodbyeHtml\Ast\Statements\LoopStatement;
use Serhii\GoodbyeHtml\Ast\Statements\Program;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\CoreParser\ParserError;
use Serhii\GoodbyeHtml\Exceptions\CoreParserException;
use Serhii\GoodbyeHtml\Lexer\Lexer;

class CoreParserTest extends TestCase
{
    /**
     * @throws CoreParserException
     */
    private function createProgram(string $input): Program
    {
        $lexer = new Lexer($input);
        $parser = new CoreParser($lexer);

        $program = $parser->parseProgram();

        $this->assertCount(1, $program->statements, 'Program must contain 1 statements');

        return $program;
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

    private static function testBoolean($bool, $val): void
    {
        self::assertInstanceOf(BooleanLiteral::class, $bool);
        self::assertSame($val, $bool->value, "Boolean must have value '{$val}', got: '{$bool->value}'");
    }

    public function testParsingVariable(): void
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

        $this->assertSame("\n    <h1>I'm not a pro, but it's only a matter of time</h1>\n", $if->block->string());
        $this->assertSame('true', $if->condition->string());
        $this->assertNull($if->elseBlock);
    }

    public function testParsingNestedIfStatement(): void
    {
        $input = <<<HTML
        {{ if \$uses_php }}You are a cool{{ if \$male }}guy{{ end }}{{ end }}
        HTML;

        /** @var IfStatement $if */
        $if = $this->createProgram($input)->statements[0];

        self::testVariable($if->condition, 'uses_php');

        $this->assertCount(2, $if->block->statements, 'Consequence must contain 2 statements');
        $this->assertInstanceOf(HtmlStatement::class, $if->block->statements[0]);
        $this->assertInstanceOf(IfStatement::class, $if->block->statements[1]);
        $this->assertNull($if->elseBlock);

        /** @var IfStatement $if */
        $if = $if->block->statements[1];

        $this->assertCount(1, $if->block->statements, 'Consequence must contain 1 statement');
        $this->assertInstanceOf(HtmlStatement::class, $if->block->statements[0]);
        $this->assertSame('guy', $if->block->statements[0]->string());
        $this->assertNull($if->elseBlock);

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

        $this->assertSame("<span>You are too young to be here</span>", $if->block->string());
        $this->assertSame("<span>You can drink beer</span>", $if->elseBlock->string());
    }

    public function testParsingElseIfStatement(): void
    {
        $input = <<<HTML
        {{ if false }}1{{ elseif false }}2{{ else if true }}3{{ else }}4{{ end }}
        HTML;

        /** @var IfStatement $if */
        $if = $this->createProgram($input)->statements[0];

        $this->assertInstanceOf(IfStatement::class, $if);

        self::testBoolean($if->condition, false);

        $this->assertCount(2, $if->elseIfBlocks, 'ElseIfs must contain 2 statements');

        /** @var IfStatement $elseIf */
        $elseIf = $if->elseIfBlocks[0];

        self::testBoolean($elseIf->condition, false);
        $this->assertSame('2', $elseIf->block->string());

        /** @var IfStatement $elseIf */
        $elseIf = $if->elseIfBlocks[1];

        self::testBoolean($elseIf->condition, true);
        $this->assertSame('3', $elseIf->block->string());

        $this->assertNotNull($if->elseBlock, 'Alternative must not be null');
        $this->assertSame('4', $if->elseBlock->string());
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

        /** @var list<HtmlStatement|ExpressionStatement> $stmts */
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

        /** @var StringLiteral $str */
        $str = $stmt->expression;

        $this->testString($str, 'hello');
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
            ['{{ 2 > 3 }}', 2, '>', 3],
            ['{{ 1 < 4 }}', 1, '<', 4],
            ['{{ 1 <= 4 }}', 1, '<=', 4],
            ['{{ 3 >= 3 }}', 3, '>=', 3],
            ['{{ 99 == "99" }}', 99, '==', '99'],
            ['{{ true === true }}', true, '===', true],
            ['{{ true != false }}', true, '!=', false],
            ['{{ 22 !== "22" }}', 22, '!==', '22'],
        ];
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
        $this->testString($ternary->trueExpression, 'container');
        $this->testString($ternary->falseExpression, '');
    }

    public function testParsingStringConcatenation(): void
    {
        $input = "{{ 'Serhii' . ' ' . 'Cho' }}";

        $lexer = new Lexer($input);
        $parser = new CoreParser($lexer);

        $program = $parser->parseProgram();

        /** @var ExpressionStatement $stmt */
        $stmt = $program->statements[0];

        /** @var InfixExpression $infix */
        $infix = $stmt->expression;

        $this->testString($infix->right, 'Cho');
        $this->assertSame('.', $infix->operator);

        /** @var InfixExpression $infix */
        $infix = $infix->left;

        $this->assertInstanceOf(InfixExpression::class, $infix);

        $this->testString($infix->left, 'Serhii');
        $this->testString($infix->right, ' ');
        $this->assertSame('.', $infix->operator);
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
            ["{{ 5 + 5 === 2 * 5 ? 'Yes' : 'No' }}", "(((5 + 5) === (2 * 5)) ? 'Yes' : 'No')"],
            ["{{ 1 + 2 * 3 }}", "(1 + (2 * 3))"],
            ["{{ 1 + (2 + 3) + 4 }}", "((1 + (2 + 3)) + 4)"],
            ["{{ (5 + 5) * 2 }}", "((5 + 5) * 2)"],
            ["{{ (5 + 5) * 2 * (5 + 5) }}", "(((5 + 5) * 2) * (5 + 5))"],
            ["{{ -(5 + 5) }}", "(-(5 + 5))"],
            ["{{ !(true == true) }}", "(!(true == true))"],
            ["{{ !(true === true) }}", "(!(true === true))"],
        ];
    }

    public function testWhenParsingIfStatementWithElseBlockBeforeElseIfBlockGivesError(): void
    {
        $this->expectException(CoreParserException::class);
        $this->expectExceptionMessage(ParserError::elseIfBlockWrongPlace());

        $this->createProgram("{{ if true }}1{{ else }}2{{ elseif true }}3{{ end }}");
    }

    public function testParsingAssignStatement(): void
    {
        $input = "{{ \$herName = 'Anna' }}";

        /** @var AssignStatement $stmt */
        $stmt = $this->createProgram($input)->statements[0];

        $this->testVariable($stmt->variable, 'herName');
        $this->testString($stmt->value, 'Anna');
        $this->assertSame("{{ \$herName = 'Anna' }}", $stmt->string());
    }

    public function testIfStatementIsPrintedCorrectly(): void
    {
        $input = <<<HTML
        {{ if true }}
            Is true
        {{ else if false }}
            Is false
        {{ else if true }}
            Is true again
        {{ else }}
            Else is here
        {{ end }}
        HTML;

        $expect = $input . "\n";

        $this->assertSame($expect, $this->createProgram($input)->string());
    }
}
