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
    public function can_loop_from_number_to_number(string $file_name): void
    {
        $parser = new Parser(self::getPath("loop/not-parsed/$file_name"), []);
        $expect = file_get_contents(self::getPath("loop/parsed/$file_name"));

        $this->assertEquals($expect, $parser->parseHtml(), "Strings not equal in file: $file_name");
    }

    public function Provider_for_can_loop_from_number_to_number(): array
    {
        return $this->getFileNames('/files/loop/not-parsed/*');
    }
}