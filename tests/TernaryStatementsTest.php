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
        ];
    }
}
