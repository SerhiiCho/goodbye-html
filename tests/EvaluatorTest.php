<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use PHPUnit\Framework\Attributes\DataProvider;
use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Evaluator\EvalError;
use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\BooleanObj;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class EvaluatorTest extends TestCase
{
    private function testEval(string $input, ?Env $env = null): Obj|null
    {
        $lexer = new Lexer($input);
        $parser = new CoreParser($lexer);
        $program = $parser->parseProgram();

        return (new Evaluator())->eval($program, $env ?? new Env());
    }

    #[DataProvider('providerForTestEvalIntegerExpression')]
    public function testEvalIntegerExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalIntegerExpression(): array
    {
        return [
            ['{{ 5 }}', '5'],
            ['{{ 190 }}', '190'],
            ['{{ -34 }}', '-34'],
            ['{{ !-1 }}', ''],
            ['{{ !0 }}', '1'],
            ['{{ !1 }}', ''],
            ['{{ !2 }}', ''],
            ['{{ !22 }}', ''],
        ];
    }

    #[DataProvider('providerForTestEvalFloatExpression')]
    public function testEvalFloatExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalFloatExpression(): array
    {
        return [
            ['{{ 3.425 }}', '3.425'],
            ['{{ 1.9 }}', '1.9'],
            ['{{ -3.34 }}', '-3.34'],
        ];
    }

    #[DataProvider('providerForTestEvalBooleanExpression')]
    public function testEvalBooleanExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalBooleanExpression(): array
    {
        return [
            ['{{ true }}', '1'], // in PHP true to string is 1
            ['{{ false }}', ''], // in PHP false to string is ''
            ['{{ !true }}', ''],
            ['{{ !false }}', '1'],
        ];
    }

    #[DataProvider('providerForTestEvalStringExpression')]
    public function testEvalStringExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated?->value());
    }

    public static function providerForTestEvalStringExpression(): array
    {
        return [
            ["{{ 'This is a string' }}", 'This is a string'],
            ['{{ "Anna Korotchaeva" }}', 'Anna Korotchaeva'],
            ["{{ 'Anna \'Korotchaeva\'' }}", "Anna 'Korotchaeva'"],
            ['{{ "Serhii \"Cho\"" }}', 'Serhii "Cho"'],
            ['{{ "Serhii" . " " . "Cho"  }}', 'Serhii Cho'],
        ];
    }

    #[DataProvider('providerForTestEvalVariable')]
    public function testEvalVariable(string $input, mixed $expect_html, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertNotNull($evaluated, 'Evaluated is null');
        $this->assertSame($expect_html, $evaluated->value());
    }

    public static function providerForTestEvalVariable(): array
    {
        return [
            ['{{ $name }}', 'Anna', new Env(['name' => new StringObj('Anna')])],
            ['{{$her_age}}', '23', new Env(['her_age' => new IntegerObj(23)])],
        ];
    }

    #[DataProvider('providerForTestEvalIfStatement')]
    public function testEvalIfStatement(string $input, string $expected, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalIfStatement(): array
    {
        return [
            [
                '{{if $name}}Ann{{end}}',
                'Ann',
                new Env(['name' => new StringObj('Anna')])
            ],
            [
                <<<HTML
                {{ if \$age }}
                    {{ if \$isMarried }}
                        Her name is {{ \$name }}, she is {{ \$age }}
                    {{ end }}
                {{ end }}
                HTML,
                "\n    \n        Her name is Anna, she is 23\n    \n",
                new Env([
                    'age'       => new IntegerObj(23),
                    'name'      => new StringObj('Anna'),
                    'isMarried' => new BooleanObj(true),
                ]),
            ],
            [
                '{{ if false }}Not{{ else }}Yes{{ end }}',
                'Yes',
            ],
            ['{{ if false }}No{{ end }}', ''],
            ['{{ if true }}1{{ elseif false }}2{{ else if false }}3{{ else }}4{{ end }}', '1'],
            ['{{ if false }}1{{ elseif true }}2{{ else if false }}3{{ else }}4{{ end }}', '2'],
            ['{{ if false }}1{{ elseif false }}2{{ else if true }}3{{ else }}4{{ end }}', '3'],
            ['{{ if false }}1{{ elseif false }}2{{ else if false }}3{{ else }}4{{ end }}', '4'],
        ];
    }

    #[DataProvider('providerForTestEvalTernaryExpression')]
    public function testEvalTernaryExpression(string $input, string $expected, Env $env): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalTernaryExpression(): array
    {
        return [
            [
                '{{ $name ? "Ann" : "Sam" }}',
                'Ann',
                new Env(['name' => new StringObj('Anna')])
            ],
            [
                '{{ $name ? "Ann" : "Sam" }}',
                'Sam',
                new Env(['name' => new StringObj('')])
            ],
            [
                '{{ !$isUgly ? "Pretty" : "Ugly" }}',
                'Ugly',
                new Env(['isUgly' => new BooleanObj(true)])
            ],
        ];
    }

    public function testEvalHtmlStatement(): void
    {
        $input = <<<HTML
        <body>
            <main>
                <h1>Hello friend</h1>
                <p>Nice to meet you</p>
            </main>
        </body>
        HTML;

        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($input, $evaluated->value());
    }

    #[DataProvider('providerForTestEvalLoopStatement')]
    public function testEvalLoopStatement(string $input, string $expected, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalLoopStatement(): array
    {
        return [
            [
                '<ul>{{ loop 1, 4 }}<li>{{ $index }}</li>{{ end }}</ul>',
                '<ul><li>1</li><li>2</li><li>3</li><li>4</li></ul>',
            ],
            [
                '<span>{{ loop -3, 18 }}{{ $index }},{{ end }}</span>',
                '<span>-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,</span>',
            ],
        ];
    }

    #[DataProvider('providerForTestErrorHandling')]
    public function testErrorHandling(string $input, string $expectMessage): void
    {
        $evaluated = $this->testEval($input);

        $this->assertInstanceOf(ErrorObj::class, $evaluated);
        $this->assertSame($expectMessage, $evaluated->value());
    }

    public static function providerForTestErrorHandling(): array
    {
        return [
            [
                '{{ loop "hello", 4 }}loop{{ end }}',
                EvalError::wrongArgumentType('loop', ObjType::INTEGER_OBJ, new StringObj('hello'))->message,
            ],
            [
                '{{ loop 3, "6" }}loop{{ end }}',
                EvalError::wrongArgumentType('loop', ObjType::INTEGER_OBJ, new StringObj('6'))->message,
            ],
            [
                '{{ $test }}',
                EvalError::variableIsUndefined(
                    new VariableExpression(new Token(TokenType::VAR, 'test'), 'test')
                )->message,
            ],
            [
                '{{ -"hello" }}',
                EvalError::operatorNotAllowed('-', new StringObj('hello'))->message,
            ],
            [
                '{{ 5 + "hello" }}',
                EvalError::infixExpressionMustBeBetweenNumbers('right', '+', new StringObj('hello'))->message,
            ],
            [
                '{{ "nice" - 24 }}',
                EvalError::infixExpressionMustBeBetweenNumbers('left', '-', new StringObj('nice'))->message,
            ],
            [
                '{{ false / true }}',
                EvalError::infixExpressionMustBeBetweenNumbers('left', '/', new BooleanObj(false))->message,
            ],
        ];
    }

    public function testEvalNull(): void
    {
        $evaluated = $this->testEval('<span>{{ null }}</span>');

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertNotNull($evaluated, 'Evaluated is null');
        $this->assertSame('<span></span>', $evaluated->value());
    }

    #[DataProvider('providerForTestEvalInfixExpressions')]
    public function testEvalInfixExpressions(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->value());
    }

    public static function providerForTestEvalInfixExpressions(): array
    {
        return [
            ["{{ 51 + 9 }}", '60'],
            ["{{ 51 - 1 }}", '50'],
            ["{{ 51 * 2 }}", '102'],
            ["{{ 100 / 4 }}", '25'],
            ["{{ 1 / 4 }}", '0.25'],
            ["{{ 10 % 2 }}", '0'],
            ["{{ 10 % 3 }}", '1'],
            ["{{ 100 / 2 * 2 }}", '100'],
            ["{{ 100 * 2 / 2 + 50 }}", '150'],
            ["{{ 234.5 + 0.5 }}", '235'],
            ["{{ 5 + 10 / 2 }}", '10'],
            ["{{ 'anna ' . 'is pretty' }}", 'anna is pretty'],
            ["{{ 'She\'s ' . 25 }}", "She's 25"],
        ];
    }
}
