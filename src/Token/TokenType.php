<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

enum TokenType: string
{
    // Special tokens
    case EOF = 'EOF';
    case ILLEGAL = 'ILLEGAL';

    // Data types
    case HTML = 'HTML';
    case INTEGER = 'INTEGER';
    case FLOAT = 'FLOAT';
    case STRING = "string";
    case TRUE = "true";
    case FALSE = "false";
    case NULL = "null";

    // Identifiers
    case VARIABLE = 'VARIABLE';
    case IDENTIFIER = 'IDENTIFIER';

    // Control flow keywords
    case IF = 'if';
    case ELSE = 'else';
    case LOOP = 'loop';
    case END = 'end';

    // Prefix operators
    case NOT = "!";

    // Infix and Prefix operators
    case SUB = '-';

    // Delimiters
    case OPENING_BRACES = '{{';
    case CLOSING_BRACES = '}}';
    case QUESTION_MARK = '?';
    case COMMA = ',';
    case COLON = ':';

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
