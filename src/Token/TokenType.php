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
    case COMMA = ',';
    case IDENTIFIER = 'IDENTIFIER';

    case LEFT_BRACES = '{{';
    case RIGHT_BRACES = '}}';
}
