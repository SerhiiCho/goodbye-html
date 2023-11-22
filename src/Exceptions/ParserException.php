<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Exceptions;

use Exception;

class ParserException extends Exception
{
    /**
     * @param non-empty-string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
