<?php

declare(strict_types=1);

use Serhii\GoodbyeHtml\Ast\BooleanLiteral;
use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfStatement;
use Serhii\GoodbyeHtml\Ast\InfixExpression;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\LoopStatement;
use Serhii\GoodbyeHtml\Ast\NullLiteral;
use Serhii\GoodbyeHtml\Ast\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\StringLiteral;
use Serhii\GoodbyeHtml\Ast\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;

/**
 * @param ExpressionStatement[] $stmt
 */
function checkForErrors(CoreParser $parser, array $stmt, int $statements): void
{
    $errors = $parser->errors();

    expect($errors)->toBeEmpty(implode("\n", $errors));
    expect($stmt)->toHaveCount($statements, "Program must contain {$statements} statements");
}


function testVariable($var, string $val): void
{
    expect($var)->toBeInstanceOf(VariableExpression::class);
    expect($var->value)->toBe($val);
}

function testString($str, string $val): void
{
    expect($str)->toBeInstanceOf(StringLiteral::class);
    expect($str->value)->toBe($val);
}

function testInteger($int, $val): void
{
    expect($int)->toBeInstanceOf(IntegerLiteral::class);
    expect($int->value)->toBe($val);
}

function testFloat($float, $val): void
{
    expect($float)->toBeInstanceOf(FloatLiteral::class);
    expect($float->value)->toBe($val);
}

function testBoolean($bool, $val): void
{
    expect($bool)->toBeInstanceOf(BooleanLiteral::class);
    expect($bool->value)->toBe($val);
}

function testLiteralExpression(Expression $expression, mixed $expected)
{
    match (gettype($expected)) {
        'string' => testString($expression, $expected),
        'integer' => testInteger($expression, $expected),
        'double' => testFloat($expression, $expected),
        'boolean' => testBoolean($expression, $expected),
        'NULL' => expect($expression)->toBeInstanceOf(NullLiteral::class),
        default => throw new Exception("Type {$expected} is not handled. Got: {$expected}"),
    };
}

test('parse variable', function () {
    $input = '{{ $userName }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var VariableExpression $var */
    $var = $program->statements[0]->expression;

    testVariable($var, 'userName');
    expect($var->tokenLiteral())->toBe('userName', "Variable must have token literal 'userName', got: '{$var->tokenLiteral()}'");
    expect($var->string())->toBe('$userName', "Variable must have string representation '\$userName', got: '{$var->string()}'");
});

test('parse html', function () {
    $input = '<div class="nice"></div>';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var HtmlStatement $stmt */
    $stmt = $program->statements[0];

    expect($stmt->string())->toBe('<div class="nice"></div>');
});

test('parse boolean literal', function (string $input, string $expect) {
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var BooleanLiteral $prefix */
    $prefix = $program->statements[0]->expression;

    expect($prefix)->toBeInstanceOf(BooleanLiteral::class);
    expect($prefix->string())->toBe($expect);
})->with(function () {
    return [
        ['{{ true }}', 'true'],
        ['{{ false }}', 'false'],
    ];
});

test('parse if statement', function () {
    $input = <<<HTML
    {{ if true }}
        <h1>I'm not a pro but it's only a matter of time</h1>
    {{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var IfStatement $if */
    $if = $program->statements[0];

    expect($if->consequence->string())->toBe("\n    <h1>I'm not a pro but it's only a matter of time</h1>\n");
    expect($if->condition->string())->toBe('true');
    expect($if->alternative)->toBeNull();
});

test('parse nested if statements', function () {
    $input = <<<HTML
    {{ if \$uses_php }}You are a cool{{ if \$male }}guy{{ end }}{{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var IfStatement */
    $if = $program->statements[0];

    testVariable($if->condition, 'uses_php');

    expect($if->consequence->statements)->toHaveCount(2, 'Consequence must contain 2 statements');
    expect($if->consequence->statements[0])->toBeInstanceOf(HtmlStatement::class);
    expect($if->consequence->statements[1])->toBeInstanceOf(IfStatement::class);
    expect($if->alternative)->toBeNull();

    $if = $if->consequence->statements[1];

    expect($if->consequence->statements)->toHaveCount(1, 'Consequence must contain 1 statement');
    expect($if->consequence->statements[0])->toBeInstanceOf(HtmlStatement::class);
    expect($if->consequence->statements[0]->string())->toBe('guy');
    expect($if->alternative)->toBeNull();

    testVariable($if->condition, 'male');
});

test('parse else statement', function () {
    $input = <<<HTML
    {{ if \$underAge }}<span>You are too young to be here</span>{{ else }}<span>You can drink beer</span>{{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var IfStatement */
    $if = $program->statements[0];

    testVariable($if->condition, 'underAge');

    expect($if->consequence->string())->toBe("<span>You are too young to be here</span>");
    expect($if->alternative->string())->toBe("<span>You can drink beer</span>");
});

test('parse integer literal', function () {
    $input = '{{ 5 }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    testInteger($stmt->expression, 5);
});

test('parse float literal', function () {
    $input = '{{ 1.40123 }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    testFloat($stmt->expression, 1.40123);
});

test('parse loop statement', function () {
    $input = <<<HTML
    {{ loop \$fr, 5 }}<li><a href="#">Link - {{ \$index }}</a></li>{{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var LoopStatement $loop */
    $loop = $program->statements[0];

    testVariable($loop->from, 'fr');
    testInteger($loop->to, 5);

    expect($loop->body->statements)->toHaveCount(3, 'Loop body must contain 3 statements');


    $stmts = $loop->body->statements;

    expect($stmts[0]->string())->toBe('<li><a href="#">Link - ');
    testVariable($stmts[1]->expression, 'index');
    expect($stmts[2]->string())->toBe("</a></li>");
});

test('parse strings', function () {
    $input = "{{ 'hello' }}";

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var StringLiteral $var */
    $str = $program->statements[0]->expression;

    testString($str, 'hello');
});

test('parse infix expressions', function (string $inp, mixed $left, string $operator, mixed $right) {
    $lexer = new Lexer($inp);
    $parser = new CoreParser($lexer);
    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var InfixExpression $infix */
    $infix = $program->statements[0]->expression;

    expect($infix)->toBeInstanceOf(InfixExpression::class);
    expect($infix->operator)->toBe($operator);

    testLiteralExpression($infix->left, $left);
    testLiteralExpression($infix->right, $right);
})->with(function () {
    return [
        ['{{ 5 + 3 }}', 5, '+', 3],
        ['{{ 123 - 23 }}', 123, '-', 23],
        ['{{ 46 * 7 }}', 46, '*', 7],
        ['{{ 89 / 1 }}', 89, '/', 1],
        ['{{ 22 % 2 }}', 22, '%', 2],
        ['{{ "nice" . "cool" }}', "nice", '.', "cool"],
    ];
});

test('parse string concatenation', function () {
    $input = "{{ 'Serhii' . ' ' . 'Cho' }}";

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var InfixExpression $infix */
    $infix = $program->statements[0]->expression;

    testString($infix->right, 'Cho');
    expect($infix->operator)->toBe('.');

    /** @var InfixExpression $infix */
    $infix = $infix->left;

    expect($infix)->toBeInstanceOf(InfixExpression::class);

    testString($infix->left, 'Serhii');
    testString($infix->right, ' ');
});

test('parse ternary expression', function () {
    $input = "{{ \$hasContainer ? 'container' : '' }}";

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var TernaryExpression $ternary */
    $ternary = $program->statements[0]->expression;

    expect($ternary)->toBeInstanceOf(TernaryExpression::class);

    testVariable($ternary->condition, 'hasContainer');
    testString($ternary->consequence, 'container');
    testString($ternary->alternative, '');
});

test('parse prefix expressions', function (string $input, string $operator, $expect) {
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var PrefixExpression $prefix */
    $prefix = $program->statements[0]->expression;

    expect($prefix)->toBeInstanceOf(PrefixExpression::class);
    expect($prefix->operator)->toBe($operator);
})->with(function () {
    return [
        ['{{ -4 }}', '-', 4],
        ['{{ -284 }}', '-', 284],
        ['{{ !true }}', '!', false],
        ['{{ !false }}', '!', true],
    ];
});

test('parse null literal', function () {
    $input = '{{ null }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var NullLiteral $null */
    $null = $program->statements[0]->expression;

    expect($null)->toBeInstanceOf(NullLiteral::class);
    expect($null->token->literal)->toBe('null');
});

test('operator precedence parsing', function (string $input, string $expect) {
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    $actual = $program->string();

    expect($actual)->toBe($expect, "Expected '{$expect}', got '{$actual}'");
})->with(function () {
    return [
        ['{{ !-1 }}', '(!(-1))'],
        ['{{ !true ? 1 : 2 }}', '((!true) ? 1 : 2)'],
        ['{{ !true ? 1 : true ? 3 : 5 }}', '((!true) ? 1 : (true ? 3 : 5))'],
    ];
});
