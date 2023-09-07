<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class ParserTest extends TestCase
{
    public function testParseHtmlMethodThrowsExceptionIfVariableNameIsNotProvided(): void
    {
        $this->expectExceptionMessage('Undefined variable $nice');

        $parser = new Parser(self::getPath('vars/2-vars'), ['first_var' => 'Text here']);
        $parser->parseHtml();
    }
}
