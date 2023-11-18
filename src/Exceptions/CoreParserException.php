<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Exceptions;

use Exception;

class CoreParserException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct('[PARSER_ERROR]: ' . $message);
    }
}
