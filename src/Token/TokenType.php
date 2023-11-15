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
    case INT = 'INTEGER';
    case FLOAT = 'FLOAT';
    case STR = "string";
    case TRUE = "true";
    case FALSE = "false";
    case NULL = "null";

    // Identifiers
    case VAR = 'VARIABLE';
    case IDENT = 'IDENTIFIER';

    // Control flow keywords
    case IF = 'if';
    case ELSE = 'else';
    case LOOP = 'loop';
    case END = 'end';

    // Prefix operators
    case NOT = "!";

    // Infix and Prefix operators
    case SUB = '-';

    // Infix operators
    case CONCAT = '.';
    case ADD = '+';
    case MUL = '*';
    case DIV = '/';
    case MOD = '%';

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
        return self::KEYWORDS[$identifier] ?? self::IDENT;
    }
}
