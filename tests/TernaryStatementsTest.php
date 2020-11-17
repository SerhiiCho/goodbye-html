<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class TernaryStatementsTest extends TestCase
{
    /**
     * @dataProvider DataProvider_for_can_parse_if_statement
     * @test
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $boolean
     *
     * @throws \Exception
     */
    public function can_parse_ternary_statement(string $expect, string $file_name, bool $boolean): void
    {
        $parser = new Parser(self::getPath("ternary/$file_name"), compact('boolean'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement(): array
    {
        return [
            ["<h1>Print this</h1>", '1-line-content', true],
            ["<h1>Do not print this</h1>", '1-line-content', false],
            ["<h1>Print this</h1>", '1-line-content-double-quotes', true],
            ["<h1>Do not print this</h1>", '1-line-content-double-quotes', false],
            ["<section class=\"some\">\n    <h1>Hello word</h1>\n</section>", '2-lines-content', true],
            ["<section class=\"some\">\n    <h1>Smile</h1>\n</section>", '2-lines-content', false],
            ["<section class=\"container\">\n    Hi\n</section>", 'inside-class-attr', true],
            ["<section class=\"\">\n    Hi\n</section>", 'inside-class-attr', false],
            ["<h1>It's me here</h1>", 'can-use-single-quote', true],
            ["<h1>It's not me here</h1>", 'can-use-single-quote', false],
            ['<h1>He said: "I am".</h1>', 'can-use-double-quote', true],
            ['<h1>She said: "Stay here!".</h1>', 'can-use-double-quote', false],
        ];
    }
}
