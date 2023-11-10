<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Evaluator\EvalError;
use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class EvaluatorTest extends TestCase
{
    /**
     * @dataProvider providerForTestEvalIntegerExpression
     */
    public function testEvalIntegerExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated->html);
    }

    public static function providerForTestEvalIntegerExpression(): array
    {
        return [
            ['{{ 5 }}', '5'],
            ['{{ 190 }}', '190'],
            ['{{ -34 }}', '-34'],
        ];
    }

    /**
     * @dataProvider providerForTestEvalStringExpression
     */
    public function testEvalStringExpression(string $input, string $expected): void
    {
        $evaluated = $this->testEval($input);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated?->html);
    }

    public static function providerForTestEvalStringExpression(): array
    {
        return [
            ["{{ 'This is a string' }}", 'This is a string'],
            ['{{ "Anna Korotchaeva" }}', 'Anna Korotchaeva'],
        ];
    }

    /**
     * @dataProvider providerForTestEvalVariable
     */
    public function testEvalVariable(string $input, mixed $expect_html, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertNotNull($evaluated, 'Evaluated is null');
        $this->assertSame($expect_html, $evaluated->inspect());
    }

    public static function providerForTestEvalVariable(): array
    {
        return [
            ['{{ $name }}', 'Anna', new Env(['name' => new StringObj('Anna')])],
            ['{{$her_age}}', '23', new Env(['her_age' => new IntegerObj(23)])],
        ];
    }

    /**
     * @dataProvider providerForTestEvalIfExpression
     */
    public function testEvalIfExpression(string $input, string $expected, Env $env): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated?->html);
    }

    public static function providerForTestEvalIfExpression(): array
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
                    {{ if \$name }}
                        Her name is {{ \$name }}, she is {{ \$age }}
                    {{ end }}
                {{ end }}
                HTML,
                'Her name is Anna, she is 23',
                new Env([
                    'age' => new IntegerObj(23),
                    'name' => new StringObj('Anna'),
                ]),
            ],
            [
                '{{ if $not }}Not{{ else }}Yes{{ end }}',
                'Yes',
                new Env(['not' => new IntegerObj(0)]),
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvalTernaryExpression
     */
    public function testEvalTernaryExpression(string $input, string $expected, Env $env): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated?->html);
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

        $this->assertSame($input, $evaluated?->html);
    }

    /**
     * @dataProvider providerForTestEvalLoopExpression
     */
    public function testEvalLoopExpression(string $input, string $expected, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        if ($evaluated instanceof ErrorObj) {
            $this->fail($evaluated->message);
        }

        $this->assertSame($expected, $evaluated?->html);
    }

    public static function providerForTestEvalLoopExpression(): array
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

    /**
     * @dataProvider providerForTestErrorHandling
     */
    public function testErrorHandling(string $input, string $expectMessage): void
    {
        $evaluated = $this->testEval($input);

        $this->assertInstanceOf(ErrorObj::class, $evaluated);
        $this->assertSame($expectMessage, $evaluated->message);
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
                EvalError::variableIsUndefined(new VariableExpression(new Token(TokenType::VARIABLE, 'test'), 'test'))->message,
            ],
            [
                '{{ -"hello" }}',
                EvalError::operatorNotAllowed('-', new StringObj('hello'))->message,
            ],
        ];
    }

    private function testEval(string $input, ?Env $env = null): Obj|null
    {
        $lexer = new Lexer($input);
        $parser = new CoreParser($lexer);
        $program = $parser->parseProgram();

        $errors = $parser->errors();

        if (!empty($errors)) {
            $this->fail(implode("\n", $errors));
        }

        $evaluator = new Evaluator();

        return $evaluator->eval($program, $env ?? new Env());
    }
}
