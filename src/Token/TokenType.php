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
    case IF = 'IF';
    case ELSE = 'ELSE';
    case END = '{{ end }}';
    case LOOP = 'LOOP';
    case STRING = "STRING";

    case COMMA = ',';
    case QUESTION_MARK = '?';
    case COLON = ':';
    case OPENING_BRACES = '{{';
    case CLOSING_BRACES = '}}';

    private const KEYWORDS = [
        'if' => self::IF,
        'else' => self::ELSE,
        'end' => self::END,
        'loop' => self::LOOP,
    ];

    public static function lookupIdentifier(string $identifier): self
    {
        return self::KEYWORDS[$identifier] ?? self::IDENTIFIER;
    }
}
