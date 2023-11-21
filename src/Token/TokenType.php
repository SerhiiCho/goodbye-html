<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

enum TokenType: string
{
    // Special tokens
    case ILLEGAL = 'ILLEGAL';
    case EOF = 'EOF';

    // Identifiers
    case VAR = 'VARIABLE';
    case IDENT = 'IDENTIFIER';

    // Literals
    case HTML = 'HTML'; // <div>
    case INT = 'INTEGER'; // 123
    case FLOAT = 'FLOAT'; // 1.2
    case STR = "STRING"; // 'foobar'

    // Operators
    case PLUS = '+';
    case MINUS = '-';
    case ASTERISK = '*';
    case SLASH = '/';
    case MODULO = '%';
    case PERIOD = '.';
    case BANG = "!";
    case ASSIGN = '=';

    // Delimiters
    case LBRACES = '{{';
    case RBRACES = '}}';
    case QUESTION = '?';
    case COLON = ':';
    case COMMA = ',';

    // Keywords
    case IF = 'IF';
    case ELSE = 'ELSE';
    case ELSEIF = 'ELSEIF';
    case END = 'END';
    case LOOP = 'LOOP';
    case TRUE = 'TRUE';
    case FALSE = 'FALSE';
    case NULL = "NULL";

    private const KEYWORDS = [
        'if' => self::IF,
        'else' => self::ELSE,
        'elseif' => self::ELSEIF,
        'end' => self::END,
        'loop' => self::LOOP,
        'true' => self::TRUE,
        'false' => self::FALSE,
        'null' => self::NULL,
    ];

    public static function lookupIdent(string $identifier): self
    {
        return self::KEYWORDS[$identifier] ?? self::IDENT;
    }
}
