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
    public function getPhpCodeFromHtml_takes_html_and_returns_object_with_var_names_and_regex_patterns(): void
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

        $result = exec_private_method(Parser::class, 'getPhpCodeFromHtml', $parser, $html);
        $this->assertEquals($expect, $result);
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
