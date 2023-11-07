<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Integer;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\Str;
use Serhii\GoodbyeHtml\Parser\Parser;

class EvaluatorTest extends TestCase
{
    /**
     * @dataProvider providerForTestEvalIntegerExpression
     */
    public function testEvalIntegerExpression(string $input, int $expected): void
    {
        $evaluated = $this->testEval($input);
        $this->testIntegerObject($evaluated, $expected);
    }

    public static function providerForTestEvalIntegerExpression(): array
    {
        return [
            ['{{ 5 }}', 5],
            ['{{ 190 }}', 190],
            ['{{ -34 }}', -34],
        ];
    }

    /**
     * @dataProvider providerForTestEvalVariable
     */
    public function testEvalVariable(string $input, mixed $expect_html, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        $this->assertNotNull($evaluated, 'Evaluated is null');
        $this->assertSame($expect_html, $evaluated->inspect());
    }

    public static function providerForTestEvalVariable(): array
    {
        return [
            ['{{ $name }}', 'Anna', new Env(['name' => new Str('Anna')])],
            ['{{$her_age}}', '23', new Env(['her_age' => new Integer(23)])],
        ];
    }

    /**
     * @dataProvider providerForTestEvalHtml
     */
    public function testEvalHtml(string $input, string $expect, ?Env $env = null): void
    {
        $evaluated = $this->testEval($input, $env);

        $this->assertNotNull($evaluated, 'Evaluated is null');
        $this->assertSame($expect, $evaluated->inspect());
    }

    public static function providerForTestEvalHtml(): array
    {
        return [
            ['<span>{{ 3 }}</span>', '<span>3</span>'],
        ];
    }

    private function testEval(string $input, ?Env $env = null): Obj|null
    {
        $lexer = new Lexer($input);
        $parser = new Parser($lexer);
        $program = $parser->parseProgram();

        $evaluator = new Evaluator();

        return $evaluator->eval($program, $env ?? new Env());
    }

    private function testIntegerObject(Obj $obj, int $expected): void
    {
        $this->assertInstanceOf(Integer::class, $obj);
        $this->assertSame($expected, $obj->value, "Object has wrong value. Got {$obj->value}, want {$expected}");
    }
}
