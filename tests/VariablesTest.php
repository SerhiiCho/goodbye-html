<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class VariablesTest extends TestCase
{
    /**
     * @dataProvider DataProvider_for_can_parse_variables
     * @test
     *
     * @param string $expect
     * @param string $file_name
     * @param array $vars
     *
     * @throws \Exception
     */
    public function can_parse_variables(string $expect, string $file_name, array $vars): void
    {
        $parser = new Parser(self::getPath("vars/$file_name"), $vars);
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function DataProvider_for_can_parse_variables(): array
    {
        return [
            ["<h1>Hi</h1>\n<p>Some text</p><span>OK</span>", '2-vars', ['first_var' => 'Hi', 'nice' => 'OK']],
            ["<h1>{{ first_var }}</h1>\n<p>Some text</p><span>{{ nice }}</span>", '2-vars-no-dollars', ['first_var' => 'Hi', 'nice' => 'OK']],
            ["<h1>\$first_var</h1>\n<p>Some text</p>\n<span>\$nice</span>", '2-vars-no-mustache', ['first_var' => 'Hi', 'nice' => 'OK']],
            ["<h1>ME</h1>\n<p>Some text</p>\n<span>you</span>", '2-vars-no-space', ['first_var' => 'ME', 'nice' => 'you']],
            ['<h1>{ $first_var }</h1><p>Some text</p><span>{ $nice }</span>', '2-vars-1-brace-on-sides', ['first_var' => 'ME', 'nice' => 'you']],
        ];
    }

    /** @test */
    public function can_parse_file_with_multiple_variables(): void
    {
        $vars = [
            'title' => 'Home page',
            'class' => 'x-container',
            'headline' => <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipisicing elit.
Ab ad aperiam, architecto, consectetur cum doloremque doloribus ea
eius in magni modi molestias nisi odio pariatur placeat voluptatibus
voluptatum. Ab commodi deleniti ea, est ipsam magnam molestiae non quos tempore unde?
TEXT,
            'footer' => '<footer><ul><li>First</li><li>Second</li></ul></footer>',
            'lang' => 'en',
        ];

        $parser = new Parser(self::getPath('vars/5-vars'), $vars);
        $expect = file_get_contents(self::getPath('vars/parsed/5-vars'));

        $this->assertEquals($expect, $parser->parseHtml());
    }
}