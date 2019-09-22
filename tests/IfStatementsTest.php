<?php declare(strict_types=1);

namespace Serhii\Tests;

use PHPUnit\Framework\TestCase;
use Serhii\HtmlParser\Parser;

class IfStatementsTest extends TestCase
{
    /** @test */
    public function can_parse_if_statement_with_1_variable_and_1_line_inside_if_statement(): void
    {
        $expect = "<div></div>\n<h1>Print this</h1>\n<div></div>";
        $parser = new Parser(get_path('if/state-with-var'), ['is_true' => true]);

        $this->assertEquals($expect, $parser->parseHtml());
    }
}