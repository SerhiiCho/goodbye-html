<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Lexer;

use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

final readonly class Lexer
{
    private int $position;
    private int $nextPosition;
    private string $char;

    public function __construct(private string $input)
    {
        $this->position = 0;
        $this->nextPosition = 0;
        $this->char = '';

        $this->advanceChar();
    }

    public function nextToken(): Token
    {
        $token = null;

        if ($this->char === '{' && $this->peekChar() === '{') {
            $this->advanceChar();
            $token = new Token(TokenType::LEFT_BRACES, '{{');
        } elseif ($this->char === '}' && $this->peekChar() === '}') {
            $this->advanceChar();
            $token = new Token(TokenType::RIGHT_BRACES, '}}');
        } elseif ($this->char === '') {
            $token = new Token(TokenType::EOF, $this->char);
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
}
