<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class LoopsTest extends TestCase
{
    /** @test */
    public function can_loop_from_0_to_10(): void
    {
        $parser = new Parser(self::getPath('loop/from-0-to-10'), []);
        $expect = file_get_contents(self::getPath('loop/parsed/from-0-to-10'));

        $this->assertEquals($expect, $parser->parseHtml());
    }
}