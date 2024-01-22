<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

enum ParserOption
{
    /**
     * If you pass this option, the parser, instead of getting
     * the content of the provided file path, will parse the
     * provided string. This option is useful when you want
     * to parse a string instead of a file.
     */
    case PARSE_TEXT;
}
