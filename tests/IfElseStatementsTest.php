<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class IfElseStatementsTest extends TestCase
{
    /**
     * @dataProvider DataProvider_for_can_parse_if_statement
     * @test
     */
    public function can_parse_if_else_statement($expect, $file_name, $boolean): void
    {
        $parser = new Parser(self::getPath("ifelse/$file_name"), compact('boolean'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement(): array
    {
        return [
            ["<div></div>\n<h1>Print this</h1>\n<div></div>", '1-line-content', true],
            ["<div></div>\n<h1>Don't print this</h1>\n<div></div>", '1-line-content', false],
            ["<div></div>\n<h1>Print this</h1>\n<p>Content</p>\n<div></div>", '2-lines-content', true],
            ["<div></div>\n<h1>Don't print this</h1>\n<p>Also content</p>\n<div></div>", '2-lines-content', false],
        ];
    }

    /**
     * @dataProvider DataProvider_for_can_parse_if_statement_when_has_another_variable_inside
     * @test
     */
    public function can_parse_if_statement_when_has_another_variable_inside($expect, $file_name, $boolean, $content, $content2): void
    {
        $parser = new Parser(self::getPath("ifelse/$file_name"), compact('boolean', 'content', 'content2'));
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

    /**
     * @dataProvider DataProvider_can_parse_if_statement_when_it_is_inline
     * @test
     */
    public function can_parse_if_statement_when_it_is_inline($expect, $file_name, $bool): void
    {
        $parser = new Parser(self::getPath("ifelse/$file_name"), compact('bool'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_can_parse_if_statement_when_it_is_inline(): array
    {
        return [
            ['<p class="my-class">Some text</p>', 'inline-statement-in-arg', true],
            ['<p class="another-class">Some text</p>', 'inline-statement-in-arg', false],
        ];
    }

    /** @test */
    public function can_parse_file_with_multiple_statements(): void
    {
        $vars = [
            'her_name' => 'Anna',
            'show_her_name' => true,
            'my_name' => 'Serhii',
            'show_my_name' => false,
            'show_class' => true,
            'my_class' => 'container',
            'lang' => 'en',
        ];

        $parser = new Parser(self::getPath('ifelse/multiple-statements'), $vars);
        $expect = file_get_contents(self::getPath('ifelse/parsed/multiple-statements'));

        $this->assertEquals($expect, $parser->parseHtml());
    }
}
