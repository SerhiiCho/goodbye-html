<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Parser;

use Closure;
use Serhii\GoodbyeHtml\Ast\BlockStatement;
use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfExpression;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\Statement;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

final class Parser
{
    private Token $curToken;
    private Token $peekToken;
    private array $prefixParseFns = [];
    private array $infixParseFns = [];

    /**
     * @var string[]
     */
    private array $errors = [];

    public function __construct(private Lexer $lexer)
    {
        $this->curToken = Token::illegal('');
        $this->peekToken = Token::illegal('');

        $this->nextToken();
        $this->nextToken();

        $this->registerPrefix(TokenType::VARIABLE, fn () => $this->parseVariable());
        $this->registerPrefix(TokenType::IF, fn () => $this->parseIfExpression());
    }

    public function parseProgram(): Program
    {
        /** @var Statement[] $statements */
        $statements = [];

        while (!$this->curTokenIs(TokenType::EOF)) {
            $stmt = $this->parseStatement();

            if ($stmt) {
                $statements[] = $stmt;
            }

            $this->nextToken();
        }

        return new Program($statements);
    }

    /**
     * @return string[]
     */
    public function errors(): array
    {
        return $this->errors;
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
            TokenType::HTML => $this->parseHtmlStatement(),
            TokenType::OPENING_BRACES => $this->parseExpressionStatement(),
            default => null,
        };
    }

    private function parseHtmlStatement(): HtmlStatement
    {
        return new HtmlStatement($this->curToken);
    }

    private function parseExpressionStatement(): ExpressionStatement
    {
        $this->nextToken();

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

        if (!$prefix) {
            $this->errors[] = "no prefix parse function for {$this->curToken->type->value} found";
            return null;
        }

        $leftExp = $prefix();

        while (!$this->peekTokenIs(TokenType::CLOSING_BRACES)) {
            $infix = $this->infixParseFns[$this->peekToken->type->value] ?? null;

            if ($infix === null) {
                return $leftExp;
            }

            $this->nextToken();

            $leftExp = $infix($leftExp);
        }

        return $leftExp;
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

    private function parseIfExpression(): Expression|null
    {
        $this->nextToken();

        $condition = $this->parseExpression();

        if (!$this->expectPeek(TokenType::CLOSING_BRACES)) {
            return null;
        }

        $this->nextToken();

        $consequence = $this->parseBlockStatement();
        $alternative = null;

        if ($this->peekTokenIs(TokenType::ELSE)) {
            $this->nextToken();

            dd($this->curToken);
            $alternative = $this->parseBlockStatement();
        }

        return new IfExpression(
            $this->curToken,
            $condition,
            $consequence,
            $alternative,
        );
    }

    private function parseBlockStatement(): BlockStatement
    {
        $statements = [];
        $token = $this->curToken;

        while (
            !$this->curTokenIs(TokenType::OPENING_BRACES) && (
                !$this->curTokenIs(TokenType::ELSE) ||
                !$this->curTokenIs(TokenType::END)
            )
        ) {
            $stmt = $this->parseStatement();

            if ($stmt) {
                $statements[] = $stmt;
            }

            $this->nextToken();
        }

        return new BlockStatement($token, $statements);
    }

    private function expectPeek(TokenType $token): bool
    {
        if ($this->peekTokenIs($token)) {
            $this->nextToken();
            return true;
        }

        $this->errors[] = sprintf(
            "expected next token to be %s, got %s instead",
            $token->value,
            $this->peekToken->type->value,
        );

        return false;
    }
}
