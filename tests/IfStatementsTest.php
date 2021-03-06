<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class IfStatementsTest extends TestCase
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
    public function can_parse_if_statement(string $expect, string $file_name, bool $boolean): void
    {
        $parser = new Parser(self::getPath("if/$file_name"), compact('boolean'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement(): array
    {
        return [
            ["<div></div>\n<h1>Print this</h1>\n<div></div>", '1-line-content', true],
            ["<div></div>\n<div></div>", '1-line-content', false],
            ["<div></div>\n<h1>Print this</h1>\n<p>Content</p>\n<div></div>", '2-lines-content', true],
        ];
    }

    /**
     * @dataProvider DataProvider_for_can_parse_if_statement_when_has_another_variable_inside
     * @test
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $boolean
     * @param string $content
     *
     * @throws \Exception
     */
    public function can_parse_if_statement_when_has_another_variable_inside(
        string $expect,
        string $file_name,
        bool $boolean,
        string $content
    ): void {
        $parser = new Parser(self::getPath("if/$file_name"), compact('boolean', 'content'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement_when_has_another_variable_inside(): array
    {
        return [
            ["<div></div>\n<div></div>", 'var-in-if', false, 'Some text is here'],
            ["<div></div>\n    Some text is here\n<div></div>", 'var-in-if', true, 'Some text is here'],
            ["<div></div>\n<div></div>", 'var-in-if-2-lines', false, 'Content'],
            ["<div></div>\n    Content\n    Content\n<div></div>", 'var-in-if-2-lines', true, 'Content'],
        ];
    }

    /**
     * @dataProvider DataProvider_can_parse_if_statement_when_it_is_inline
     * @test
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $bool
     *
     * @throws \Exception
     */
    public function can_parse_if_statement_when_it_is_inline(string $expect, string $file_name, bool $bool): void
    {
        $parser = new Parser(self::getPath("if/$file_name"), compact('bool'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_can_parse_if_statement_when_it_is_inline(): array
    {
        return [
            ['<p class="my-class">Some text</p>', 'inline-statement-in-arg', true],
            ['<p class="">Some text</p>', 'inline-statement-in-arg', false],
        ];
    }

    /** @test */
    public function can_parse_file_with_multiple_statements(): void
    {
        $vars = [
            'her_name' => 'Anna',
            'show_her_name' => true,
            'my_name' => 'Serhii',
            'show_my_name' => true,
            'show_class' => true,
            'my_class' => 'container',
            'lang' => 'en',
            'show_lang' => true,
        ];

        $parser = new Parser(self::getPath('if/multiple-statements'), $vars);
        $expect = file_get_contents(self::getPath('if/parsed/multiple-statements'));

        $this->assertEquals($expect, $parser->parseHtml());
    }
}