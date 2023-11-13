<?php

declare(strict_types=1);

use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Evaluator\EvalError;
use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Obj\BooleanObj;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

test('eval integer expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalIntegerExpression');

dataset('providerForTestEvalIntegerExpression', function () {
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
});

test('eval float expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalFloatExpression');

dataset('providerForTestEvalFloatExpression', function () {
    return [
        ['{{ 3.425 }}', '3.425'],
        ['{{ 1.9 }}', '1.9'],
        ['{{ -3.34 }}', '-3.34'],
    ];
});

test('eval boolean expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalBooleanExpression');

dataset('providerForTestEvalBooleanExpression', function () {
    return [
        ['{{ true }}', '1'], // in PHP true to string is 1
        ['{{ false }}', ''], // in PHP false to string is ''
        ['{{ !true }}', ''],
        ['{{ !false }}', '1'],
    ];
});

test('eval string expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated?->value())->toBe($expected);
})->with('providerForTestEvalStringExpression');

dataset('providerForTestEvalStringExpression', function () {
    return [
        ["{{ 'This is a string' }}", 'This is a string'],
        ['{{ "Anna Korotchaeva" }}', 'Anna Korotchaeva'],
        ["{{ 'Anna \'Korotchaeva\'' }}", "Anna 'Korotchaeva'"],
        ['{{ "Serhii \"Cho\"" }}', 'Serhii "Cho"'],
    ];
});

test('eval variable', function (string $input, mixed $expect_html, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated)->not->toBeNull('Evaluated is null');
    expect($evaluated->value())->toBe($expect_html);
})->with('providerForTestEvalVariable');

dataset('providerForTestEvalVariable', function () {
    return [
        ['{{ $name }}', 'Anna', new Env(['name' => new StringObj('Anna')])],
        ['{{$her_age}}', '23', new Env(['her_age' => new IntegerObj(23)])],
    ];
});

test('eval if expression', function (string $input, string $expected, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalIfExpression');

dataset('providerForTestEvalIfExpression', function () {
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
                'age' => new IntegerObj(23),
                'name' => new StringObj('Anna'),
                'isMarried' => new BooleanObj(true),
            ]),
        ],
        [
            '{{ if false }}Not{{ else }}Yes{{ end }}',
            'Yes',
        ],
    ];
});

test('eval ternary expression', function (string $input, string $expected, Env $env) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalTernaryExpression');

dataset('providerForTestEvalTernaryExpression', function () {
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
});

test('eval html statement', function () {
    $input = <<<HTML
    <body>
        <main>
            <h1>Hello friend</h1>
            <p>Nice to meet you</p>
        </main>
    </body>
    HTML;

    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($input);
});

test('eval loop expression', function (string $input, string $expected, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with('providerForTestEvalLoopExpression');

dataset('providerForTestEvalLoopExpression', function () {
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
});

test('error handling', function (string $input, string $expectMessage) {
    $evaluated = testEval($input);

    expect($evaluated)->toBeInstanceOf(ErrorObj::class);
    expect($evaluated->value())->toBe($expectMessage);
})->with('providerForTestErrorHandling');

dataset('providerForTestErrorHandling', function () {
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
});

test('eval null test', function () {
    $evaluated = testEval('<span>{{ null }}</span>');

    if ($evaluated instanceof ErrorObj) {
        $this->fail($evaluated->message);
    }

    expect($evaluated)->not->toBeNull('Evaluated is null');
    expect($evaluated->value())->toBe('<span></span>');
});

function testEval(string $input, ?Env $env = null)
{
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);
    $program = $parser->parseProgram();

    $errors = $parser->errors();

    expect($errors)->toBeEmpty(implode("\n", $errors));

    $evaluator = new Evaluator();

    return $evaluator->eval($program, $env ?? new Env());
}
