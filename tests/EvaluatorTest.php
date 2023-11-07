<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Integer;
use Serhii\GoodbyeHtml\Obj\Obj;
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
        ];
    }

    private function testEval(string $input): Obj
    {
        $lexer = new Lexer($input);
        $parser = new Parser($lexer);
        $program = $parser->parseProgram();
        $env = new Env();

        $evaluator = new Evaluator();

        return $evaluator->eval($program, $env);
    }

    private function testIntegerObject(Obj $obj, int $expected): void
    {
        $this->assertInstanceOf(Integer::class, $obj);
        $this->assertSame($expected, $obj->value, "Object has wrong value. Got {$obj->value}, want {$expected}");
    }
}
