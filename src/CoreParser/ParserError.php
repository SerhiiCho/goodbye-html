<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

abstract class ParserError
{
    private const PREFIX = '[PARSER_ERROR]:';

    public static function noPrefixParseFunction(Token $token): string
    {
        return sprintf(
            '%s no prefix parse function for character "%s" found',
            self::PREFIX,
            $token->literal,
        );
    }

    public static function expectNextTokenToBeDifferent(TokenType $token, Token $peek): string
    {
        return sprintf(
            '%s expected next token to be %s, got %s instead',
            self::PREFIX,
            $token->value,
            $peek->type->value,
        );
    }

    public static function elseIfBlockWrongPlace(): string
    {
        return sprintf(
            '%s Wrong placement of the "elseif" block! "elseif" block must be placed after "if" or "elseif" block',
            self::PREFIX,
        );
    }
}
