<?php

declare(strict_types=1);

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

function testEval(string $input, ?Env $env = null): Obj
{
    $lexer = new Lexer($input);
    $parser = new CoreParser($lexer);
    $program = $parser->parseProgram();

    return (new Evaluator())->eval($program, $env ?? new Env());
}

it('evaluates integer expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
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

it('evaluates float expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
    return [
        ['{{ 3.425 }}', '3.425'],
        ['{{ 1.9 }}', '1.9'],
        ['{{ -3.34 }}', '-3.34'],
    ];
});

it('evaluates boolean literal', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
    return [
        ['{{ true }}', '1'], // in PHP true to string is 1
        ['{{ false }}', ''], // in PHP false to string is ''
        ['{{ !true }}', ''],
        ['{{ !false }}', '1'],
    ];
});

it('evaluates) string expression', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
    return [
        ["{{ 'This is a string' }}", 'This is a string'],
        ['{{ "Anna Korotchaeva" }}', 'Anna Korotchaeva'],
        ["{{ 'Anna \'Korotchaeva\'' }}", "Anna 'Korotchaeva'"],
        ['{{ "Serhii \"Cho\"" }}', 'Serhii "Cho"'],
        ['{{ "Serhii" . " " . "Cho"  }}', 'Serhii Cho'],
    ];
});

it('evaluates variable', function (string $input, mixed $expect_html, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated)
        ->not
        ->toBeNull('Evaluated is null')
        ->and($evaluated->value())
        ->toBe($expect_html);
})->with(function () {
    return [
        ['{{ $name }}', 'Anna', new Env(['name' => new StringObj('Anna')])],
        ['{{$her_age}}', '23', new Env(['her_age' => new IntegerObj(23)])],
    ];
});

it('evaluates if statement', function (string $input, string $expected, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
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

it('evaluates ternary expression', function (string $input, string $expected, Env $env) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
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

it('evaluates html statement', function () {
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
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($input);
});

it('evaluates loop statement', function (string $input, string $expected, ?Env $env = null) {
    $evaluated = testEval($input, $env);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
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

it('has a proper error handling', function (string $input, string $expectMessage) {
    $evaluated = testEval($input);

    expect($evaluated)
        ->toBeInstanceOf(ErrorObj::class)
        ->and($evaluated->value())
        ->toBe($expectMessage);
})->with(function () {
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
            EvalError::variableIsUndefined(new VariableExpression(new Token(TokenType::VAR, 'test'), 'test'))->message,
        ],
        [
            '{{ -"hello" }}',
            EvalError::operatorNotAllowed('-', new StringObj('hello'))->message,
        ],
    ];
});

it('evaluates null test', function () {
    $evaluated = testEval('<span>{{ null }}</span>');

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated)
        ->not
        ->toBeNull('Evaluated is null')
        ->and($evaluated->value())
        ->toBe('<span></span>');
});

it('evaluates infix expressions', function (string $input, string $expected) {
    $evaluated = testEval($input);

    if ($evaluated instanceof ErrorObj) {
        throw new Error($evaluated->message);
    }

    expect($evaluated->value())->toBe($expected);
})->with(function () {
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
});
