<?php

declare(strict_types=1);

namespace Serhii\Tests;

use PHPUnit\Framework\TestCase;
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
        $parser = new Parser(get_path("ternary/$file_name"), compact('boolean'));
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

    /**
     * @dataProvider DataProvider_for_can_parse_if_statement_when_has_another_variable_inside
     * @test
     */
    public function can_parse_if_statement_when_has_another_variable_inside($expect, $file_name, $boolean, $content, $content2): void
    {
        $parser = new Parser(get_path("ifelse/$file_name"), compact('boolean', 'content', 'content2'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement_when_has_another_variable_inside(): array
    {
        return [
            ["<div></div>\nSome text is here\n<div></div>", 'var-in-if', true, 'Some text is here', 'Another text'],
            ["<div></div>\nAnother text\n<div></div>", 'var-in-if', false, 'Some text is here', 'Another text'],
            ["<div></div>\nContent\n    Content\n<div></div>", 'var-in-if-2-lines', true, 'Content', 'Other'],
            ["<div></div>\nOther\n    Other\n<div></div>", 'var-in-if-2-lines', false, 'Content', 'Other'],
        ];
    }
}
