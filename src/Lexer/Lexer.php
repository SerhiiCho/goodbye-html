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
    private string $char = '';
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

        if ($this->char === '{' && $this->peekChar() === '{') {
            $this->advanceChar();
            $this->isHtml = false;
            $token = new Token(TokenType::LEFT_BRACES, '{{');
        } elseif ($this->char === '}' && $this->peekChar() === '}') {
            $this->advanceChar();
            $this->isHtml = true;
            $token = new Token(TokenType::RIGHT_BRACES, '}}');
        } elseif (!$this->isHtml && $this->char === '$' && $this->isLetter($this->peekChar())) {
            $this->advanceChar();
            return new Token(TokenType::VARIABLE, $this->readIdentifier());
        } elseif ($this->char === '') {
            $token = new Token(TokenType::EOF, $this->char);
        } elseif ($this->isHtml) {
            $token = new Token(TokenType::HTML, $this->readHtml());
        } else {
            $token = new Token(TokenType::ILLEGAL, $this->char);
        }

        $this->advanceChar();

        return $token;
    }

    private function advanceChar(): void
    {
        if ($this->nextPosition >= strlen($this->input)) {
            $this->char = '';
        } else {
            $this->char = $this->input[$this->nextPosition];
        }

        $this->position = $this->nextPosition;
        $this->nextPosition += 1;
    }

    private function peekChar(): string
    {
        if ($this->nextPosition >= strlen($this->input)) {
            return '';
        }

        return $this->input[$this->nextPosition];
    }

    private function isLetter(string $letter): bool
    {
        return preg_match('/[a-zA-Z]/', $letter) === 1;
    }

    private function readIdentifier(): string
    {
        $position = $this->position;

        while ($this->isLetter($this->char)) {
            $this->advanceChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    private function readHtml(): string
    {
        $result = '';

        while ($this->isHtml && ($this->char !== '{' && $this->peekChar() !== '{')) {
            if ($this->char === '') {
                break;
            }

            if (!$this->isWhitespace()) {
                $result .= $this->char;
            }

            $this->advanceChar();
        }

        $result .= $this->char;

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
}
