<?php declare(strict_types=1);

namespace Serhii\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Serhii\HtmlParser\Parser;

class ParserTest extends TestCase
{
    /** @test */
    public function Parser_has_properties(): void
    {
        $this->assertClassHasAttribute('html_string', Parser::class);
        $this->assertClassHasAttribute('variables', Parser::class);
    }

    /** @test */
    public function replaceVarNamesWithValues_takes_array_with_var_names_and_returns_var_values(): void
    {
        $before = ['first_variable', 'second_variable'];
        $after = ['Content of the first', 'Content of the second'];

        $parser = new Parser(get_path('empty'), [$before[0] => $after[0], $before[1] => $after[1]]);

        $result = exec_private_method(Parser::class, 'replaceVarNamesWithValues', $parser, $before);

        $this->assertSame($after, $result);
    }
    
    /** @test */
    public function getVariablesFromHtml_takes_html_and_returns_object_with_var_names_and_regex_patterns(): void
    {
        $parser = new Parser(get_path('empty'), []);
        $html = '<h1>{{ $first_var }}</h1><p>Some text</p><span>{{ $nice }}</span>';
        $expect = (object) [
            'regex_patterns' => [
                '/{{ \$first_var }}/',
                '/{{ \$nice }}/',
            ],
            'var_names' => [
                'first_var',
                'nice',
            ],
        ];

        $result = exec_private_method(Parser::class, 'getVariablesFromHtml', $parser, $html);
        $this->assertEquals($expect, $result);
    }

    /** @test */
    public function parseHtml_replaces_all_php_variables_with_values(): void
    {
        $expect = "<h1>Text for first variable</h1>\n<p>Some text</p><span>For nice variable</span>";

        $parser = new Parser(get_path('2-vars'), [
            'first_var' => 'Text for first variable',
            'nice' => 'For nice variable'
        ]);

        $this->assertEquals($expect, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function parseHtml_replaces_all_php_variables_with_values_even_if_no_space_before_and_after_vars(): void
    {
        $expect = "<h1>Text for first variable</h1>\n<p>Some text</p><span>For nice variable</span>";

        $parser = new Parser(get_path('2-vars-no-space'), [
            'first_var' => 'Text for first variable',
            'nice' => 'For nice variable'
        ]);

        $this->assertEquals($expect, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function parseHtml_is_not_replacing_variables_without_mustache_braces(): void
    {
        $parser = new Parser(get_path('2-vars-no-mustache'));
        $html = file_get_contents(get_path('2-vars-no-mustache'));

        $this->assertEquals($html, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function parseHtml_is_not_replacing_variables_without_dollar_signs(): void
    {
        $parser = new Parser(get_path('2-vars-no-dollars'));
        $html = file_get_contents(get_path('2-vars-no-dollars'));

        $this->assertEquals($html, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function parseHtml_is_not_replacing_variables_with_single_mustache_brace_on_both_sides(): void
    {
        $parser = new Parser(get_path('2-vars-on-brace-on-sides'));
        $html = file_get_contents(get_path('2-vars-on-brace-on-sides'));

        $this->assertEquals($html, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function parseHtml_is_not_replacing_variables_without_mustache_braces_on_one_side_of_the_var(): void
    {
        $parser = new Parser(get_path('2-vars-on-brace-on-side'));
        $html = file_get_contents(get_path('2-vars-on-brace-on-side'));

        $this->assertEquals($html, exec_private_method(Parser::class, 'parseHtml', $parser));
    }

    /** @test */
    public function replaceVarNamesWithValues_method_throws_exception_if_variable_name_is_not_provided(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Undefined variable $nice');

        $provided_variables = ['first_var' => 'Text here'];
        $found_variables = ['first_var', 'nice'];

        $parser = new Parser(get_path('empty'), $provided_variables);

        exec_private_method(Parser::class, 'replaceVarNamesWithValues', $parser, $found_variables);
    }
}
