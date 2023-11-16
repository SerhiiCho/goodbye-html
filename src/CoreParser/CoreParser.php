<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

use Closure;
use Serhii\GoodbyeHtml\Ast\BlockStatement;
use Serhii\GoodbyeHtml\Ast\BooleanExpression;
use Serhii\GoodbyeHtml\Ast\Expression;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfStatement;
use Serhii\GoodbyeHtml\Ast\InfixExpression;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\LoopExpression;
use Serhii\GoodbyeHtml\Ast\NullLiteral;
use Serhii\GoodbyeHtml\Ast\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\Statement;
use Serhii\GoodbyeHtml\Ast\StringLiteral;
use Serhii\GoodbyeHtml\Ast\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Token\Token;
use Serhii\GoodbyeHtml\Token\TokenType;

class CoreParser
{
    private const PRECEDENCES = [
        // TokenType::EQUAL->value => Precedence::EQUALS,
        // TokenType::NOT_EQUAL->value => Precedence::EQUALS,
        // TokenType::LESS_THAN->value => Precedence::LESS_GREATER,
        // TokenType::GREATER_THAN->value => Precedence::LESS_GREATER,
        TokenType::QUESTION->value => Precedence::TERNARY,
        TokenType::PERIOD->value => Precedence::SUM,
        TokenType::PLUS->value => Precedence::SUM,
        TokenType::MINUS->value => Precedence::SUM,
        TokenType::SLASH->value => Precedence::PRODUCT,
        TokenType::MODULO->value => Precedence::PRODUCT,
        TokenType::ASTERISK->value => Precedence::PRODUCT,
        // TokenType::LEFT_PAREN->value => Precedence::CALL,
        // TokenType::LEFT_BRACKET->value => Precedence::INDEX,
    ];

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

        // Prefix operators
        $this->registerPrefix(TokenType::VAR, fn () => $this->parseVariable());
        $this->registerPrefix(TokenType::IF, fn () => $this->parseIfStatement());
        $this->registerPrefix(TokenType::LOOP, fn () => $this->parseLoopExpression());
        $this->registerPrefix(TokenType::INT, fn () => $this->parseIntegerLiteral());
        $this->registerPrefix(TokenType::BANG, fn () => $this->parsePrefixExpression());
        $this->registerPrefix(TokenType::NULL, fn () => $this->parseNullLiteral());
        $this->registerPrefix(TokenType::FLOAT, fn () => $this->parseFloatLiteral());
        $this->registerPrefix(TokenType::STR, fn () => $this->parseStringLiteral());
        $this->registerPrefix(TokenType::TRUE, fn () => $this->parseBoolean());
        $this->registerPrefix(TokenType::FALSE, fn () => $this->parseBoolean());
        $this->registerPrefix(TokenType::MINUS, fn () => $this->parsePrefixExpression());

        // Infix operators
        $this->registerInfix(TokenType::QUESTION, fn ($l) => $this->parseTernaryExpression($l));
        $this->registerInfix(TokenType::PERIOD, fn ($l) => $this->parseInfixExpression($l));
        $this->registerInfix(TokenType::PLUS, fn ($l) => $this->parseInfixExpression($l));
        $this->registerInfix(TokenType::MINUS, fn ($l) => $this->parseInfixExpression($l));
        $this->registerInfix(TokenType::SLASH, fn ($l) => $this->parseInfixExpression($l));
        $this->registerInfix(TokenType::ASTERISK, fn ($l) => $this->parseInfixExpression($l));
        $this->registerInfix(TokenType::MODULO, fn ($l) => $this->parseInfixExpression($l));
    }

    /**
     * Main entry point of the parser
     *
     * <program>
     *   : <statement>*
     *   ;
     */
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

    private function curTokenIs(TokenType $token): bool
    {
        return $token === $this->curToken->type;
    }

    private function peekTokenIs(TokenType $token): bool
    {
        return $token === $this->peekToken->type;
    }

    private function currentPrecedence(): Precedence
    {
        return self::PRECEDENCES[$this->curToken->type->value] ?? Precedence::LOWEST;
    }

    private function peekPrecedence(): Precedence
    {
        return self::PRECEDENCES[$this->peekToken->type->value] ?? Precedence::LOWEST;
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

    /**
     * <statement>
     *   : <html-statement>
     *   | <expression-statement>
     *   ;
     */
    private function parseStatement(): Statement|null
    {
        return match($this->curToken->type) {
            TokenType::HTML => $this->parseHtmlStatement(),
            TokenType::LBRACES => $this->parseExpressionStatement(),
            default => null,
        };
    }

    /**
     * <html-statement>
     *   : <html>
     *   ;
     */
    private function parseHtmlStatement(): HtmlStatement
    {
        return new HtmlStatement($this->curToken);
    }

    /**
     * <expression-statement>
     *   : <expression>
     *   ;
     */
    private function parseExpressionStatement(): ExpressionStatement
    {
        $this->nextToken();

        $expr = $this->parseExpression(Precedence::LOWEST);
        $result = new ExpressionStatement($this->curToken, $expr);

        if ($this->peekTokenIs(TokenType::RBRACES)) {
            $this->nextToken();
        }

        return $result;
    }

    /**
     * <expression>
     *   : <prefix-expression> <infix-expression>*
     *   ;
     */
    private function parseExpression(Precedence $precedence): Expression|null
    {
        $prefix = $this->prefixParseFns[$this->curToken->type->value] ?? null;

        if (!$prefix) {
            $this->errors[] = sprintf(
                '[PARSER_ERROR] no prefix parse function for character "%s" found',
                $this->curToken->literal,
            );

            return null;
        }

        $leftExp = $prefix();

        while (
            !$this->peekTokenIs(TokenType::RBRACES)
            && $precedence->value < $this->peekPrecedence()->value
        ) {
            $infix = $this->infixParseFns[$this->peekToken->type->value] ?? null;

            if ($infix === null) {
                return $leftExp;
            }

            $this->nextToken();

            $leftExp = $infix($leftExp);
        }

        return $leftExp;
    }

    /**
     * <variable-expression>
     *   : <variable>
     *   ;
     */
    private function parseVariable(): Expression
    {
        return new VariableExpression(
            $this->curToken,
            $this->curToken->literal,
        );
    }

    /**
     * <integer-literal>
     *   : <integer>
     *   ;
     */
    private function parseIntegerLiteral(): Expression
    {
        return new IntegerLiteral(
            $this->curToken,
            (int) $this->curToken->literal,
        );
    }

    /**
     * <null-literal>
     *   : "null"
     *   ;
     */
    private function parseNullLiteral(): Expression
    {
        return new NullLiteral($this->curToken);
    }

    /**
     * <float-literal>
     *   : <float>
     *   ;
     */
    private function parseFloatLiteral(): Expression
    {
        return new FloatLiteral(
            $this->curToken,
            (float) $this->curToken->literal,
        );
    }

    /**
     * <string-literal>
     *   : <string>
     *   ;
     */
    private function parseStringLiteral(): Expression
    {
        return new StringLiteral(
            $this->curToken,
            $this->curToken->literal,
        );
    }

    /**
     * <prefix-expression>
     *   : <prefix-operator> <expression>
     *   ;
     */
    private function parsePrefixExpression(): Expression
    {
        $token = $this->curToken;
        $operator = $this->curToken->literal;

        $this->nextToken(); // skip prefix operator

        $right = $this->parseExpression(Precedence::PREFIX);

        return new PrefixExpression($token, $operator, $right);
    }

    /**
     * <boolean-expression>
     *   : "true"
     *   | "false"
     *   ;
     */
    private function parseBoolean(): Expression
    {
        return new BooleanExpression(
            token: $this->curToken,
            value: $this->curToken->literal === 'true',
        );
    }

    /**
     * <if-statement>
     *   : "{{" "if" <expression> "}}" <block-statement> "{{" "end" "}}"
     *   | "{{" "if" <expression> "}}" <block-statement> "{{" "else" "}}" <block-statement> "{{" "end" "}}"
     *   ;
     */
    private function parseIfStatement(): Expression|null
    {
        $this->nextToken(); // skip "{{"

        $condition = $this->parseExpression(Precedence::LOWEST);

        if (!$this->expectPeek(TokenType::RBRACES)) {
            return null;
        }

        $this->nextToken(); // skip "}}"

        $consequence = $this->parseBlockStatement();
        $alternative = null;

        if ($this->peekTokenIs(TokenType::ELSE)) {
            $this->nextToken(); // skip "{{"
            $this->nextToken(); // skip "else"
            $this->nextToken(); // skip "}}"

            $alternative = $this->parseBlockStatement();
        }

        return new IfStatement(
            $this->curToken,
            $condition,
            $consequence,
            $alternative,
        );
    }

    /**
     * <infix-expression>
     *   : <expression> <operator> <expression>
     *   ;
     */
    private function parseInfixExpression(Expression $left): Expression
    {
        $token = $this->curToken;
        $operator = $this->curToken->literal;

        $precedence = $this->currentPrecedence();

        $this->nextToken();

        $right = $this->parseExpression($precedence);

        return new InfixExpression(
            token: $token,
            left: $left,
            operator: $operator,
            right: $right,
        );
    }

    /**
     * <ternary-expression>
     *   : <expression> "?" <expression> ":" <expression>
     *   ;
     */
    private function parseTernaryExpression(Expression $left): Expression|null
    {
        $this->nextToken(); // skip "?"

        $consequence = $this->parseExpression(Precedence::TERNARY);

        if (!$this->expectPeek(TokenType::COLON)) {
            return null;
        }

        $this->nextToken(); // skip ":"

        $alternative = $this->parseExpression(Precedence::LOWEST);

        $this->nextToken(); // skip alternative

        return new TernaryExpression(
            token: $this->curToken,
            condition: $left,
            consequence: $consequence,
            alternative: $alternative,
        );
    }

    /**
     * <loop-expression>
     *   : "{{" "loop" <expression> "," <expression> "}}" <block-statement> "{{" "end" "}}"
     *   ;
     */
    private function parseLoopExpression(): Expression|null
    {
        $token = $this->curToken;

        $this->nextToken(); // skip "loop" keyword

        $from = $this->parseExpression(Precedence::LOWEST);

        if (!$this->expectPeek(TokenType::COMMA)) {
            return null;
        }

        $this->nextToken();

        $to = $this->parseExpression(Precedence::LOWEST);

        if (!$this->expectPeek(TokenType::RBRACES)) {
            return null;
        }

        $this->nextToken();

        $body = $this->parseBlockStatement();

        if (!$this->expectPeek(TokenType::END)) { // skip "{{"
            return null;
        }

        if (!$this->expectPeek(TokenType::RBRACES)) { // skip "end"
            return null;
        }

        return new LoopExpression($token, $from, $to, $body);
    }

    /**
     * <block-statement>
     *   : <statement>*
     *   ;
     */
    private function parseBlockStatement(): BlockStatement
    {
        $statements = [];
        $token = $this->curToken;

        while (true) {
            $isOpening = $this->curTokenIs(TokenType::LBRACES);
            $isPeekEnd = $this->peekTokenIs(TokenType::END);
            $isPeekElse = $this->peekTokenIs(TokenType::ELSE);

            if ($isOpening && ($isPeekEnd || $isPeekElse)) {
                break;
            }

            $stmt = $this->parseStatement();

            if ($stmt) {
                $statements[] = $stmt;
            }

            $this->nextToken();
        }

        return new BlockStatement($token, $statements);
    }
}
