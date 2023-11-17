<?php

declare(strict_types=1);

use Serhii\GoodbyeHtml\Parser;

test('parser evaluation', function (string $fileName, array $variables) {
    $fileToParse = __DIR__ . "/files/before/{$fileName}.html";

    $parser = new Parser($fileToParse, $variables);

    try {
        $actual = $parser->parseHtml();

        $expect = file_get_contents(__DIR__ . "/files/expect/{$fileName}.html");

        expect($actual)->toBe($expect, "Failed asserting that {$fileName}.html is parsed correctly");
    } catch (Throwable $e) {
        $this->fail($e->getMessage());
    }
})->with(function () {
    return [
        ['if', ['isSecondary' => 3, 'title' => 'Pretty title', 'showList' => 2]],
        ['loop', ['to' => 3]],
        ['ternary', ['hasContainer' => true]],
        ['readme', ['title' => 'Title of the document', 'uses_php_3_years' => true, 'show_container' => false]],
        ['types', ['weight' => 61.5, 'eyeColor' => null, 'smart' => true, 'tall' => false]],
    ];
});

test('parser can parse text directly', function () {
    $input = <<<TEXT
    She is {{ if \$isNice }}nice{{ else }}not nice{{ end }}.
    He has {{ \$cats ? \$cats : 'no' }} cats.
    He is {{ !false ? 'funny' : 'not funny' }}.
    {{ \$name . ' and ' . 'Shayla' . ' are from the movie ' . \$movie . '!' }}
    TEXT;

    $expect = <<<TEXT
    She is nice.
    He has no cats.
    He is funny.
    Elliot and Shayla are from the movie Mr. Robot!
    TEXT;

    $parser = new Parser($input, [
        'isNice' => true,
        'cats' => null,
        'name' => 'Elliot',
        'movie' => 'Mr. Robot',
    ]);

    try {
        expect($parser->parseHtml())->toBe($expect);
    } catch (Throwable $e) {
        $this->fail($e->getMessage());
    }
});
