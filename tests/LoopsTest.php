<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;
use Exception;

class LoopsTest extends TestCase
{
    /**
     * @dataProvider Provider_for_can_loop_from_number_to_number
     *
     *
     * @param string $file_name
     *
     * @throws Exception
     */
    public function testCanLoopWithNumbersAsParameters(string $file_name): void
    {
        $parser = new Parser(self::getPath("loop/not-parsed/{$file_name}"), []);
        $expect = file_get_contents(self::getPath("loop/parsed/{$file_name}"));

        $this->assertEquals($expect, $parser->parseHtml(), "Strings not equal in file: {$file_name}");
    }

    public function Provider_for_can_loop_from_number_to_number(): array
    {
        return $this->getFileNames('/files/loop/not-parsed/*');
    }

    /**
     * @dataProvider Provider_for_can_loop_with_variables_as_parameters
     *
     * @param string $expect
     * @param string $file_name
     * @param int|null $num1
     * @param int|null $num2
     *
     * @throws Exception
     */
    public function testCanLoopWithVariablesAsParameters(string $expect, string $file_name, ?int $num1, ?int $num2): void
    {
        $parser = new Parser(self::getPath("loop/with-vars/{$file_name}"), compact('num1', 'num2'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function Provider_for_can_loop_with_variables_as_parameters(): array
    {
        return [
            ["<span>1</span>\n<span>2</span>\n<span>3</span>\n", 'with-2-vars', 1, 3],
            ["<span>1</span>\n<span>2</span>\n", 'with-first-var', 1, null],
            ["<span>0</span>\n<span>1</span>\n<span>2</span>\n<span>3</span>\n", 'with-second-var', null, 3],
            ["<section>\n<span>1</span>\n<span>2</span>\n</section>\n<div>\n<h2></h2>\n<h2></h2>\n</div>", 'with-2-loops', 1, 2],
        ];
    }


    public function testReplaceArgumentVariablesThrowsExceptionIfSecondLoopArgumentIsNotProvided(): void
    {
        $this->expectExceptionMessage('Undefined variable $num2');

        $parser = new Parser(self::getPath("loop/with-vars/with-2-vars"), ['num1' => 0]);
        $parser->parseHtml();
    }


    public function testReplaceArgumentVariablesThrowsExceptionIfFirstLoopArgumentIsNotProvided(): void
    {
        $this->expectExceptionMessage('Undefined variable $num1');

        $parser = new Parser(self::getPath("loop/with-vars/with-2-vars"), ['num2' => 2]);
        $parser->parseHtml();
    }
}
