<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class LoopsTest extends TestCase
{
    /**
     * @dataProvider Provider_for_can_loop_from_number_to_number
     * @test
     * @param int $from
     * @param int $to
     *
     * @throws \Exception
     */
    public function can_loop_from_number_to_number(int $from, int $to): void
    {
        $parser = new Parser(self::getPath("loop/from-$from-to-$to"), []);
        $expect = file_get_contents(self::getPath("loop/parsed/from-$from-to-$to"));

        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function Provider_for_can_loop_from_number_to_number(): array
    {
        return [[0, 10], [1, 17], [0, 5], [10, 20]];
    }
}