<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Lexer;

use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

final class Lexer
{
    private readonly string $input;
    private int $position = 0;
    private int $nextPosition = 0;
    private string|int $char = '';
    private bool $isHtml = true;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->advanceChar();
    }

    public function nextToken(): Token
    {
        if (!$this->isHtml) {
            $this->skipWhitespace();
        }

        if ($this->char === 0) {
            return new Token(TokenType::EOF, '');
        } elseif ($this->areOpeningBraces()) {
            $this->isHtml = false;
            $this->advanceChar();
            $this->advanceChar();
            return new Token(TokenType::OPENING_BRACES, '{{');
        } elseif ($this->areClosingBraces()) {
            $this->isHtml = true;
            $this->advanceChar();
            $this->advanceChar();
            return new Token(TokenType::CLOSING_BRACES, '}}');
        }

        return $this->isHtml
            ? $this->readHtmlToken()
            : $this->readProgramToken();
    }

    private function readProgramToken(): Token
    {
        $token = null;

        if ($this->char === '-') {
            $token = new Token(TokenType::MINUS, $this->char);
            $this->advanceChar();
        } elseif ($this->char === '$' && $this->isLetter($this->peekChar())) {
            $this->advanceChar();
            $token = new Token(TokenType::VARIABLE, $this->readIdentifier());
        } elseif ($this->char === ',') {
            $token = new Token(TokenType::COMMA, $this->char);
            $this->advanceChar();
        } elseif ($this->char === '?') {
            $token = new Token(TokenType::QUESTION_MARK, $this->char);
            $this->advanceChar();
        } elseif ($this->char === ':') {
            $token = new Token(TokenType::COLON, $this->char);
            $this->advanceChar();
        } elseif ($this->char === "'" || $this->char === '"') {
            $quote = $this->char;
            $this->advanceChar();
            $token = new Token(TokenType::STRING, $this->readString($quote));
            $this->advanceChar();
        } elseif ($this->isLetter($this->char)) {
            $ident = $this->readIdentifier();
            $type = TokenType::lookupIdentifier($ident);
            $token = new Token($type, $ident);
        } elseif ($this->isNumber($this->char)) {
            $token = new Token(TokenType::INTEGER, $this->readNumber());
        } else {
            $token = Token::illegal($this->char);
            $this->advanceChar();
        }

        return $token;
    }

    private function readHtmlToken(): Token
    {
        if ($this->char === 0) {
            $token = new Token(TokenType::EOF, 'EOF');
        } else {
            $token = new Token(TokenType::HTML, $this->readHtml());
        }

        $this->advanceChar();

        return $token;
    }

    private function areOpeningBraces(): bool
    {
        return $this->char === '{' && $this->peekChar() === '{';
    }

    private function areClosingBraces(): bool
    {
        return $this->char === '}' && $this->peekChar() === '}';
    }

    private function advanceChar(): void
    {
        if ($this->nextPosition >= strlen($this->input)) {
            $this->char = 0;
        } else {
            $this->char = $this->input[$this->nextPosition];
        }

        $this->position = $this->nextPosition;
        $this->nextPosition += 1;
    }

    private function peekChar(): string|int
    {
        if ($this->nextPosition >= strlen($this->input)) {
            return 0;
        }

        return $this->input[$this->nextPosition];
    }

    private function isLetter(string $letter): bool
    {
        return preg_match('/[_a-zA-Z]/', $letter) === 1;
    }

    private function isNumber(int|string $number): bool
    {
        if ($number === 0) {
            return false;
        }

        return preg_match('/[0-9]/', $number) === 1;
    }

    private function readIdentifier(): string
    {
        $position = $this->position;

        while ($this->isLetter($this->char) || $this->isNumber($this->char)) {
            $this->advanceChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    private function readNumber(): string
    {
        $position = $this->position;

        while ($this->isNumber($this->char)) {
            $this->advanceChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    private function readHtml(): string
    {
        $result = '';

        while ($this->isHtml && ($this->char !== '{' && $this->peekChar() !== '{')) {
            if ($this->char === 0) {
                break;
            }

            $result .= $this->char;

            $this->advanceChar();
        }

        if ($this->char !== 0) {
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

    private function readString(string $quote): string
    {
        if ($this->char === $quote) {
            return '';
        }

        $position = $this->position;

        while ($this->char !== $quote) {
            $this->advanceChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }
}
