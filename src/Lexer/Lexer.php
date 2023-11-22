<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Lexer;

use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class Lexer
{
    private const LAST_CHAR = 'ø';

    /**
     * The position of the current character in the input (points to current char)
     *
     * @var int<0, max>
     */
    private int $position = 0;

    /**
     * The position of the next character in the input (points to next char)
     *
     * @var int<0, max>
     */
    private int $nextPosition = 0;

    /**
     * The current character under examination
     */
    private string $char = '';

    /**
     * Tells us whether we are in HTML or in embedded code
     */
    private bool $isHtml = true;

    public function __construct(private readonly string $input)
    {
        $this->advanceChar();
    }

    public function nextToken(): Token
    {
        if (!$this->isHtml) {
            $this->skipWhitespace();
        }

        if ($this->char === self::LAST_CHAR) {
            return new Token(TokenType::EOF, '');
        }

        if ($this->char === '{' && $this->peekChar() === '{') {
            $this->isHtml = false;
            $this->advanceChar();
            $this->advanceChar();
            return new Token(TokenType::LBRACES, '{{');
        }

        if ($this->char === '}' && $this->peekChar() === '}') {
            $this->isHtml = true;
            $this->advanceChar();
            $this->advanceChar();
            return new Token(TokenType::RBRACES, '}}');
        }

        return $this->isHtml
            ? $this->readHtmlToken()
            : $this->readEmbeddedCodeToken();
    }

    private function readEmbeddedCodeToken(): Token
    {
        $token = match ($this->char) {
            '+' => $this->createTokenAndAdvanceChar(TokenType::PLUS),
            '-' => $this->createTokenAndAdvanceChar(TokenType::MINUS),
            '*' => $this->createTokenAndAdvanceChar(TokenType::ASTERISK),
            '/' => $this->createTokenAndAdvanceChar(TokenType::SLASH),
            '%' => $this->createTokenAndAdvanceChar(TokenType::MODULO),
            ',' => $this->createTokenAndAdvanceChar(TokenType::COMMA),
            '?' => $this->createTokenAndAdvanceChar(TokenType::QUESTION),
            ':' => $this->createTokenAndAdvanceChar(TokenType::COLON),
            '!' => $this->createTokenAndAdvanceChar(TokenType::BANG),
            '.' => $this->createTokenAndAdvanceChar(TokenType::PERIOD),
            '=' => $this->createTokenAndAdvanceChar(TokenType::ASSIGN),
            default => false,
        };

        if ($token) {
            return $token;
        }

        if ($this->isVariableStart()) {
            $this->advanceChar(); // skip "$"
            return new Token(TokenType::VAR, $this->readIdentifier());
        }

        if ($this->isStringStart()) {
            return $this->createTokenAndAdvanceChar(TokenType::STR, $this->readString());
        }

        if ($this->isLetter($this->char)) {
            $ident = $this->readIdentifier();
            return new Token(TokenType::lookupIdent($ident), $ident);
        }

        if ($this->isNumber($this->char)) {
            $num = $this->readNumber();
            return new Token($this->readNumberTokenType($num), $num);
        }

        return $this->createTokenAndAdvanceChar(TokenType::ILLEGAL);
    }

    private function isStringStart(): bool
    {
        return $this->char === "'" || $this->char === '"';
    }

    private function isVariableStart(): bool
    {
        return $this->char === '$' && $this->isLetter($this->peekChar());
    }

    private function createTokenAndAdvanceChar(TokenType $type, ?string $char = null): Token
    {
        $char ??= $this->char;
        $this->advanceChar();
        return new Token($type, $char);
    }

    private function readNumberTokenType(string $num): TokenType
    {
        // If number contains more then one dot, the token is ILLEGAL
        if (substr_count($num, '.') > 1) {
            return TokenType::ILLEGAL;
        }

        return str_contains($num, '.') ? TokenType::FLOAT : TokenType::INT;
    }

    private function readHtmlToken(): Token
    {
        if ($this->char === self::LAST_CHAR) {
            $token = new Token(TokenType::EOF, 'EOF');
        } else {
            $token = new Token(TokenType::HTML, $this->readHtml());
        }

        $this->advanceChar();

        return $token;
    }

    private function advanceChar(): void
    {
        if ($this->nextPosition >= strlen($this->input)) {
            $this->char = self::LAST_CHAR;
        } else {
            $this->char = $this->input[$this->nextPosition];
        }

        $this->position = $this->nextPosition;
        ++$this->nextPosition;
    }

    /**
     * @return non-empty-string
     */
    private function peekChar(): string
    {
        if ($this->nextPosition >= strlen($this->input)) {
            return self::LAST_CHAR;
        }

        /** @var non-empty-string */
        return $this->input[$this->nextPosition];
    }

    private function isLetter(string $letter): bool
    {
        return preg_match('/[_a-zA-Z]/', $letter) === 1;
    }

    private function isInteger(string $number): bool
    {
        if ($number === self::LAST_CHAR) {
            return false;
        }

        // The number must not contain a dot
        if (str_contains($number, '.')) {
            return false;
        }

        return preg_match('/\d/', $number) === 1;
    }

    private function isNumber(string $number): bool
    {
        if ($number === self::LAST_CHAR) {
            return false;
        }

        return preg_match('/[0-9.]/', $number) === 1;
    }

    /**
     * @return non-empty-string
     */
    private function readIdentifier(): string
    {
        $position = $this->position;

        while ($this->isLetter($this->char) || $this->isInteger($this->char)) {
            $this->advanceChar();
        }

        /** @var non-empty-string $result */
        $result = substr($this->input, $position, $this->position - $position);

        // Handle case when identifier is "else if"
        if ($result === 'else' && $this->peekChar() === 'i') {
            $this->advanceChar(); // skip " "
            $result .= $this->char; // add "i"
            $this->advanceChar(); // skip "i"
            $result .= $this->char; // add "f"
            $this->advanceChar(); // skip "f"
        }

        return $result;
    }

    /**
     * @return non-empty-string
     */
    private function readNumber(): string
    {
        $position = $this->position;

        while ($this->isInteger($this->char) || $this->char === '.') {
            $this->advanceChar();
        }

        /** @var non-empty-string */
        return substr($this->input, $position, $this->position - $position);
    }

    private function readHtml(): string
    {
        $result = '';

        while ($this->isHtml && ($this->char !== '{' && $this->peekChar() !== '{')) {
            if ($this->char === self::LAST_CHAR) {
                break;
            }

            $result .= $this->char;

            $this->advanceChar();
        }

        if ($this->char !== self::LAST_CHAR) {
            $result .= $this->char;
        }

        return $result;
    }

    private function isWhitespace(): bool
    {
        return $this->char === "\n" || $this->char === "\r" || $this->char === " " || $this->char === "\t";
    }

    private function skipWhitespace(): void
    {
        while ($this->isWhitespace()) {
            $this->advanceChar();
        }
    }

    private function readString(): string
    {
        $quote = $this->char;

        $this->advanceChar();

        if ($this->char === $quote) {
            return '';
        }

        $position = $this->position;

        while (true) {
            $prevChar = $this->char;

            $this->advanceChar();

            if ($this->char === $quote && $prevChar !== '\\') {
                break;
            }
        }

        $result = substr($this->input, $position, $this->position - $position);

        // remove slashes before quotes
        return str_replace('\\' . $quote, $quote, $result);
    }
}
