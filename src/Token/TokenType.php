<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

enum TokenType: string
{
    case EOF = 'EOF';
    case VARIABLE = 'VARIABLE';
    case ILLEGAL = 'ILLEGAL';
    case HTML = 'HTML';
    case INTEGER = 'INTEGER';
    case IDENTIFIER = 'IDENTIFIER';
    case STRING = "STRING";

    case COMMA = ',';
    case QUESTION_MARK = '?';
    case COLON = ':';
    case OPENING_BRACES = '{{';
    case CLOSING_BRACES = '}}';
}
