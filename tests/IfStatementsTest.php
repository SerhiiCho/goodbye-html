<?php declare(strict_types=1);

namespace Serhii\Tests;

use PHPUnit\Framework\TestCase;
use Serhii\HtmlParser\Parser;

class IfStatementsTest extends TestCase
{
    /**
     * @dataProvider DataProvider_for_can_parse_if_statement
     * @test
     */
    public function can_parse_if_statement($expect, $file_name, $boolean): void
    {
        $parser = new Parser(get_path("if/$file_name"), compact('boolean'));

        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement(): array
    {
        return [
            ["<div></div>\n<h1>Print this</h1>\n<div></div>", '1-line-content', true],
            ["<div></div>\n\n<div></div>", '1-line-content', false],
            ["<div></div>\n<h1>Print this</h1>\n<p>Content</p>\n<div></div>", '2-lines-content', true],
        ];
    }

    /**
     * @dataProvider DataProvider_for_can_parse_if_statement_when_has_another_variable_inside
     * @test
     */
    public function can_parse_if_statement_when_has_another_variable_inside($expect, $file_name, $boolean, $content): void
    {
        $parser = new Parser(get_path("if/$file_name"), compact('boolean', 'content'));

        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement_when_has_another_variable_inside(): array
    {
        return [
            ["<div></div>\n\n<div></div>", 'var-in-if', false, 'Some text is here'],
            ["<div></div>\nSome text is here\n<div></div>", 'var-in-if', true, 'Some text is here'],
            ["<div></div>\n\n<div></div>", 'var-in-if-2-lines', false, 'Content'],
            ["<div></div>\nContent\n    Content\n<div></div>", 'var-in-if-2-lines', true, 'Content'],
        ];
    }
}