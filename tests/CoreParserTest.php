<?php

declare(strict_types=1);

use Serhii\GoodbyeHtml\Ast\BooleanExpression;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfExpression;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\LoopExpression;
use Serhii\GoodbyeHtml\Ast\NullLiteral;
use Serhii\GoodbyeHtml\Ast\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\StringLiteral;
use Serhii\GoodbyeHtml\Ast\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;

test('parsing variables', function () {
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

test('parsing html', function () {
    $input = '<div class="nice"></div>';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var HtmlStatement $stmt */
    $stmt = $program->statements[0];

    expect($stmt->string())->toBe('<div class="nice"></div>');
});

test('boolean expressions', function (string $input, string $expect) {
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var BooleanExpression $prefix */
    $prefix = $program->statements[0]->expression;

    expect($prefix)->toBeInstanceOf(BooleanExpression::class);
    expect($prefix->string())->toBe($expect);
})->with('providerForTestBooleanExpressions');

dataset('providerForTestBooleanExpressions', function () {
    return [
        ['{{ true }}', 'true'],
        ['{{ false }}', 'false'],
    ];
});

test('parsing if expression', function () {
    $input = <<<HTML
    {{ if true }}
        <h1>I'm not a pro but it's only a matter of time</h1>
    {{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    /** @var IfExpression */
    $if = $stmt->expression;

    expect($if->consequence->string())->toBe("\n    <h1>I'm not a pro but it's only a matter of time</h1>\n");
    expect($if->condition->string())->toBe('true');
    expect($if->alternative)->toBeNull();
});

test('parsing nested if statement', function () {
    $input = <<<HTML
    {{ if \$uses_php }}You are a cool{{ if \$male }}guy{{ end }}{{ end }}
    HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    /** @var IfExpression */
    $if = $stmt->expression;

    testVariable($if->condition, 'uses_php');

    expect($if->consequence->statements)->toHaveCount(2, 'Consequence must contain 2 statements');
    expect($if->consequence->statements[0])->toBeInstanceOf(HtmlStatement::class);
    expect($if->consequence->statements[1])->toBeInstanceOf(ExpressionStatement::class);
    expect($if->alternative)->toBeNull();

    /** @var IfExpression $if */
    $if = $if->consequence->statements[1]->expression;

    expect($if->consequence->statements)->toHaveCount(1, 'Consequence must contain 1 statement');
    expect($if->consequence->statements[0])->toBeInstanceOf(HtmlStatement::class);
    expect($if->consequence->statements[0]->string())->toBe('guy');
    expect($if->alternative)->toBeNull();

    testVariable($if->condition, 'male');
});

test('parsing else statement', function () {
    $input = <<<HTML
        {{ if \$underAge }}<span>You are too young to be here</span>{{ else }}<span>You can drink beer</span>{{ end }}
        HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var IfExpression */
    $if = $program->statements[0]->expression;

    testVariable($if->condition, 'underAge');

    expect($if->consequence->string())->toBe("<span>You are too young to be here</span>");
    expect($if->alternative->string())->toBe("<span>You can drink beer</span>");
});

test('parsing integer literal', function () {
    $input = '{{ 5 }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    testInteger($stmt->expression, 5);
});

test('parsing float literal', function () {
    $input = '{{ 1.40123 }}';

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var ExpressionStatement $stmt */
    $stmt = $program->statements[0];

    testFloat($stmt->expression, 1.40123);
});

test('parsing loop expression', function () {
    $input = <<<HTML
        {{ loop \$fr, 5 }}<li><a href="#">Link - {{ \$index }}</a></li>{{ end }}
        HTML;

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var LoopExpression $loop */
    $loop = $program->statements[0]->expression;

    testVariable($loop->from, 'fr');
    testInteger($loop->to, 5);

    expect($loop->body->statements)->toHaveCount(3, 'Loop body must contain 3 statements');

    $stmts = $loop->body->statements;

    expect($stmts[0]->string())->toBe('<li><a href="#">Link - ');
    testVariable($stmts[1]->expression, 'index');
    expect($stmts[2]->string())->toBe("</a></li>");
});

test('parsing strings', function () {
    $input = "{{ 'hello' }}";

    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var StringLiteral $var */
    $str = $program->statements[0]->expression;

    testString($str, 'hello');
});

test('parsing ternary expression', function () {
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

test('prefix expressions', function (string $input, string $operator, $expect) {
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);

    $program = $parser->parseProgram();

    checkForErrors($parser, $program->statements, 1);

    /** @var PrefixExpression $prefix */
    $prefix = $program->statements[0]->expression;

    expect($prefix)->toBeInstanceOf(PrefixExpression::class);
    expect($prefix->operator)->toBe($operator);
})->with('providerForTestPrefixExpressions');

dataset('providerForTestPrefixExpressions', function () {
    return [
        ['{{ -4 }}', '-', 4],
        ['{{ -284 }}', '-', 284],
        ['{{ !true }}', '!', false],
        ['{{ !false }}', '!', true],
    ];
});

/**
 * @param ExpressionStatement[] $stmt
 */
function checkForErrors(CoreParser $parser, array $stmt, int $statements): void
{
    $errors = $parser->errors();

    expect($errors)->toBeEmpty(implode("\n", $errors));
    expect($stmt)->toHaveCount($statements, "Program must contain {$statements} statements");
}

function testVariable($var, string $val)
{
    expect($var)->toBeInstanceOf(VariableExpression::class);
    expect($var->value)->toBe($val);
}

function testString($str, string $val)
{
    expect($str)->toBeInstanceOf(StringLiteral::class);
    expect($str->value)->toBe($val);
}

function testInteger($int, $val)
{
    expect($int)->toBeInstanceOf(IntegerLiteral::class);
    expect($int->value)->toBe($val);
}

function testFloat($float, $val)
{
    expect($float)->toBeInstanceOf(FloatLiteral::class);
    expect($float->value)->toBe($val);
}

test('test parsing null', function () {
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
})->with('providerForTestOperatorPrecedenceParsing');

dataset('providerForTestOperatorPrecedenceParsing', function () {
    return [
        ['{{ !-1 }}', '(!(-1))'],
        ['{{ !true ? 1 : 2 }}', '((!true) ? 1 : 2)'],
        ['{{ !true ? 1 : true ? 3 : 5 }}', '((!true) ? 1 : (true ? 3 : 5))'],
    ];
});
