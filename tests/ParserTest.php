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
            // ['if', ['isSecondary' => 3, 'title' => 'Pretty title', 'showList' => 2]],
            // ['loop', ['to' => 3]],
            // ['ternary', ['hasContainer' => true]],
            ['all', ['title' => 'Title of the document', 'uses_php_3_years' => true, 'show_container' => false]],
        ];
    }
}
