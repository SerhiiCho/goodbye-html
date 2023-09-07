<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;
use Exception;

class IfElseStatementsTest extends TestCase
{
    /**
     * @dataProvider DataProvider_for_can_parse_if_statement
     *
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $boolean
     *
     * @throws Exception
     */
    public function testCanParseIfElseStatement(string $expect, string $file_name, bool $boolean): void
    {
        $parser = new Parser(self::getPath("ifelse/{$file_name}"), compact('boolean'));
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
     *
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $boolean
     * @param string $content
     * @param string $content2
     *
     * @throws Exception
     */
    public function testCanParseIfStatementWhenHasAnotherVariableInside(
        string $expect,
        string $file_name,
        bool $boolean,
        string $content,
        string $content2
    ): void {
        $parser = new Parser(self::getPath("ifelse/{$file_name}"), compact('boolean', 'content', 'content2'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_if_statement_when_has_another_variable_inside(): array
    {
        return [
            ["<div></div>\n    Some text is here\n<div></div>", 'var-in-if', true, 'Some text is here', 'Another text'],
            ["<div></div>\n    Another text\n<div></div>", 'var-in-if', false, 'Some text is here', 'Another text'],
            ["<div></div>\n    Content\n    Content\n<div></div>", 'var-in-if-2-lines', true, 'Content', 'Other'],
            ["<div></div>\n    Other\n    Other\n<div></div>", 'var-in-if-2-lines', false, 'Content', 'Other'],
        ];
    }

    /**
     * @dataProvider DataProvider_can_parse_if_statement_when_it_is_inline
     *
     *
     * @param string $expect
     * @param string $file_name
     * @param bool $bool
     *
     * @throws Exception
     */
    public function testCanParseIfStatementWhenItIsInline(string $expect, string $file_name, bool $bool): void
    {
        $parser = new Parser(self::getPath("ifelse/{$file_name}"), compact('bool'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_can_parse_if_statement_when_it_is_inline(): array
    {
        return [
            ['<p class="my-class">Some text</p>', 'inline-statement-in-arg', true],
            ['<p class="another-class">Some text</p>', 'inline-statement-in-arg', false],
        ];
    }


    public function testCanParseFileWithMultipleStatements(): void
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
