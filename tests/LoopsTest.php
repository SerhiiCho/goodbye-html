<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class LoopsTest extends TestCase
{
    /**
     * @dataProvider Provider_for_can_loop_from_number_to_number
     * @test
     *
     * @param string $file_name
     *
     * @throws \Exception
     */
    public function can_loop_with_numbers_as_parameters(string $file_name): void
    {
        $parser = new Parser(self::getPath("loop/not-parsed/$file_name"), []);
        $expect = file_get_contents(self::getPath("loop/parsed/$file_name"));

        $this->assertEquals($expect, $parser->parseHtml(), "Strings not equal in file: $file_name");
    }

    public function Provider_for_can_loop_from_number_to_number(): array
    {
        return $this->getFileNames('/files/loop/not-parsed/*');
    }

    /**
     * @dataProvider Provider_for_can_loop_with_variables_as_parameters
     * @test
     * @param string $expect
     * @param string $file_name
     * @param int|null $num1
     * @param int|null $num2
     *
     * @throws \Exception
     */
    public function can_loop_with_variables_as_parameters(string $expect, string $file_name, ?int $num1, ?int $num2): void
    {
        $parser = new Parser(self::getPath("loop/with-vars/$file_name"), compact('num1', 'num2'));
        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function Provider_for_can_loop_with_variables_as_parameters(): array
    {
        return [
            ["<span>1</span>\n<span>2</span>\n<span>3</span>\n", 'with-2-vars', 1, 3],
            ["<span>1</span>\n<span>2</span>\n", 'with-first-var', 1, null],
        ];
    }

    /** @test */
    public function replaceArgumentVariables_throws_exception_if_second_loop_argument_is_not_provided(): void
    {
        $this->expectExceptionMessage('Undefined variable $num2');

        $parser = new Parser(self::getPath("loop/with-vars/with-2-vars"), ['num1' => 0]);
        $parser->parseHtml();
    }

    /** @test */
    public function replaceArgumentVariables_throws_exception_if_first_loop_argument_is_not_provided(): void
    {
        $this->expectExceptionMessage('Undefined variable $num1');

        $parser = new Parser(self::getPath("loop/with-vars/with-2-vars"), ['num2' => 2]);
        $parser->parseHtml();
    }
}