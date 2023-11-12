<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class ParserTest extends TestCase
{
    /**
     * @dataProvider providerForTestParserEvaluation
     */
    public function testParserEvaluation(string $fileName, array $variables): void
    {
        $fileToParse = __DIR__ . "/files/before/{$fileName}.html";

        $parser = new Parser($fileToParse, $variables);

        $actual = $parser->parseHtml();

        $expect = file_get_contents(__DIR__ . "/files/expect/{$fileName}.html");

        $this->assertSame($expect, $actual, "Failed asserting that {$fileName}.html is parsed correctly");
    }

    public static function providerForTestParserEvaluation(): array
    {
        return [
            ['if', ['isSecondary' => 3, 'title' => 'Pretty title', 'showList' => 2]],
            ['loop', ['to' => 3]],
            ['ternary', ['hasContainer' => true]],
            ['readme', ['title' => 'Title of the document', 'uses_php_3_years' => true, 'show_container' => false]],
            ['types', ['weight' => 61.5, 'eyeColor' => null, 'smart' => true, 'tall' => false]],
        ];
    }

    public function testParserCanParseTextDirectly(): void
    {
        $input = <<<TEXT
        She is {{ if \$isNice }}nice{{ else }}not nice{{ end }}.
        He has {{ \$cats ? \$cats : 'no' }} cats.
        TEXT;

        $expect = <<<TEXT
        She is nice.
        He has no cats.
        TEXT;

        $parser = new Parser($input, [
            'isNice' => true,
            'cats' => null,
        ]);

        $this->assertSame($expect, $parser->parseHtml());
    }
}
