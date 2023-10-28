<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Parser;

use Closure;
use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\Statement;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

final readonly class Parser
{
    private Token $curToken;
    private Token $peekToken;
    private array $prefixParseFns;
    private array $infixParseFns;

    /**
     * @var string[]
     */
    private array $errors;

    public function __construct(private Lexer $lexer)
    {
        $this->errors = [];
        $this->prefixParseFns = [];
        $this->infixParseFns = [];

        $this->nextToken();
        $this->nextToken();

        $this->registerPrefix(TokenType::VARIABLE, fn () => $this->parseVariable());
    }

    public function parseProgram(): Program
    {
        /** @var Statement[] $statements */
        $statements = [];

        while (!$this->curTokenIs(TokenType::EOF)) {
            $stmt = $this->parseStatement();

            if ($stmt !== null) {
                $statements[] = $stmt;
            }

            $this->nextToken();
        }

        return new Program($statements);
    }

    private function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
    }

    private function registerPrefix(TokenType $token, Closure $fn): void
    {
        $this->prefixParseFns[$token->value] = $fn;
    }

    private function registerInfix(TokenType $token, Closure $fn): void
    {
        $this->infixParseFns[$token->value] = $fn;
    }

    private function parseStatement(): Statement|null
    {
        return match($this->curToken->type) {
            default => $this->parseExpressionStatement(),
        };
    }

    private function parseExpressionStatement(): ExpressionStatement
    {
        $expr = $this->parseExpression();
        $result = new ExpressionStatement($this->curToken, $expr);

        if ($this->peekTokenIs(TokenType::CLOSING_BRACES)) {
            $this->nextToken();
        }

        return $result;
    }

    private function parseExpression(): Expression|null
    {
        $prefix = $this->prefixParseFns[$this->curToken->type->value] ?? null;

        if ($prefix === null) {
            // todo: error
            return null;
        }

        $leftExp = $prefix();

        // todo: here
    }

    private function curTokenIs(TokenType $token): bool
    {
        return $token === $this->curToken->type;
    }

    private function peekTokenIs(TokenType $token): bool
    {
        return $token === $this->peekToken->type;
    }

    private function parseVariable(): Expression
    {
        return new VariableExpression(
            $this->curToken,
            $this->curToken->literal,
        );
    }
}
