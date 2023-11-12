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
    case FLOAT = 'FLOAT';
    case IDENTIFIER = 'IDENTIFIER';

    case IF = 'if';
    case ELSE = 'else';
    case LOOP = 'loop';
    case END = 'end';
    case STRING = "string";
    case TRUE = "true";
    case FALSE = "false";
    case NULL = "null";
    case NOT = "!";

    case OPENING_BRACES = '{{';
    case CLOSING_BRACES = '}}';

    case COMMA = ',';
    case QUESTION_MARK = '?';
    case COLON = ':';
    case MINUS = '-';

    private const KEYWORDS = [
        'if' => self::IF,
        'else' => self::ELSE,
        'end' => self::END,
        'loop' => self::LOOP,
        'true' => self::TRUE,
        'false' => self::FALSE,
        'null' => self::NULL,
    ];

    public static function lookupIdentifier(string $identifier): self
    {
        return self::KEYWORDS[$identifier] ?? self::IDENTIFIER;
    }
}
