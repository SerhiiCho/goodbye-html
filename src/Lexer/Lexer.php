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
        $token = null;

        $this->skipWhitespace();

        if ($this->char === 0) {
            $token = new Token(TokenType::EOF, 'EOF');
        } elseif ($this->char === '{' && $this->peekChar() === '{') {
            $this->advanceChar();
            $token = new Token(TokenType::OPENING_BRACES, '{{');
        } elseif ($this->char === '}' && $this->peekChar() === '}') {
            $this->advanceChar();
            $token = new Token(TokenType::CLOSING_BRACES, '}}');
        } elseif (!$this->isHtml && $this->char === '$' && $this->isLetter($this->peekChar())) {
            $this->advanceChar();
            return new Token(TokenType::VARIABLE, $this->readIdentifier());
        } elseif ($this->char === ',') {
            $token = new Token(TokenType::COMMA, $this->char);
        } elseif ($this->char === '?') {
            $token = new Token(TokenType::QUESTION_MARK, $this->char);
        } elseif ($this->char === ':') {
            $token = new Token(TokenType::COLON, $this->char);
        } elseif ($this->char === "'") {
            $this->advanceChar();
            $token = new Token(TokenType::STRING, $this->readString());
        } elseif ($this->isLetter($this->char) && !$this->isHtml) {
            $ident = $this->readIdentifier();
            $type = TokenType::lookupIdentifier($ident);
            return new Token($type, $ident);
        } elseif ($this->isNumber($this->char)) {
            return new Token(TokenType::INTEGER, $this->readNumber());
        } elseif ($this->isHtml) {
            $token = new Token(TokenType::HTML, $this->readHtml());
        } else {
            $token = Token::illegal($this->char);
        }

        if ($token?->type === TokenType::CLOSING_BRACES) {
            $this->isHtml = true;
        } elseif ($token?->type === TokenType::OPENING_BRACES) {
            $this->isHtml = false;
        }

        $this->advanceChar();

        return $token;
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

    private function isNumber(string $number): bool
    {
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

    private function readString(): string
    {
        if ($this->char === "'") {
            return '';
        }

        $position = $this->position;

        while ($this->char !== "'") {
            $this->advanceChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }
}
