<?php declare(strict_types=1);

namespace Serhii\Tests;

use PHPUnit\Framework\TestCase;
use Serhii\GoodbyeHtml\Parser;

class VariablesTest extends TestCase
{
    /** @test */
    public function replaces_all_php_variables_with_values(): void
    {
        $expect = "<h1>Text for first variable</h1>\n<p>Some text</p><span>For nice variable</span>";

        $parser = new Parser(get_path('2-vars'), [
            'first_var' => 'Text for first variable',
            'nice' => 'For nice variable'
        ]);

        $this->assertEquals($expect, $parser->parseHtml());
    }

    /** @test */
    public function replaces_all_php_variables_with_values_even_if_no_space_before_and_after_vars(): void
    {
        $expect = "<h1>Text for first variable</h1>\n<p>Some text</p><span>For nice variable</span>";

        $parser = new Parser(get_path('2-vars-no-space'), [
            'first_var' => 'Text for first variable',
            'nice' => 'For nice variable'
        ]);

        $this->assertEquals($expect, $parser->parseHtml());
    }

    /** @test */
    public function is_not_replacing_variables_without_mustache_braces(): void
    {
        $parser = new Parser(get_path('2-vars-no-mustache'));
        $html = file_get_contents(get_path('2-vars-no-mustache'));

        $this->assertEquals($html, $parser->parseHtml());
    }

    /** @test */
    public function is_not_replacing_variables_without_dollar_signs(): void
    {
        $parser = new Parser(get_path('2-vars-no-dollars'));
        $html = file_get_contents(get_path('2-vars-no-dollars'));

        $this->assertEquals($html, $parser->parseHtml());
    }

    /** @test */
    public function is_not_replacing_variables_with_single_mustache_brace_on_both_sides(): void
    {
        $parser = new Parser(get_path('2-vars-on-brace-on-sides'));
        $html = file_get_contents(get_path('2-vars-on-brace-on-sides'));

        $this->assertEquals($html, $parser->parseHtml());
    }

    /** @test */
    public function is_not_replacing_variables_without_mustache_braces_on_one_side_of_the_var(): void
    {
        $parser = new Parser(get_path('2-vars-on-brace-on-side'));
        $html = file_get_contents(get_path('2-vars-on-brace-on-side'));

        $this->assertEquals($html, $parser->parseHtml());
    }
}