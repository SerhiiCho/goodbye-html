<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Exception;
use Serhii\GoodbyeHtml\Parser;

class ParserTest extends TestCase
{
    /** @test */
    public function parseHtml_method_throws_exception_if_variable_name_is_not_provided(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Undefined variable $nice');

        $parser = new Parser(self::getPath('vars/2-vars'), ['first_var' => 'Text here']);
        $parser->parseHtml();
    }
}