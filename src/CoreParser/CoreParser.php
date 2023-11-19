<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

use Closure;
use Serhii\GoodbyeHtml\Ast\Expressions\Expression;
use Serhii\GoodbyeHtml\Ast\Expressions\InfixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\Ast\Literals\BooleanLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\NullLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\StringLiteral;
use Serhii\GoodbyeHtml\Ast\Statements\BlockStatement;
use Serhii\GoodbyeHtml\Ast\Statements\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\Statements\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\Statements\IfStatement;
use Serhii\GoodbyeHtml\Ast\Statements\LoopStatement;
use Serhii\GoodbyeHtml\Ast\Statements\Program;
use Serhii\GoodbyeHtml\Ast\Statements\Statement;
use Serhii\GoodbyeHtml\Exceptions\CoreParserException;
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

    /**
     * @var array<string,Closure>
     */
    private array $prefixParseFns = [];

    /**
     * @var array<string,Closure>
     */
    private array $infixParseFns = [];

    public function __construct(private readonly Lexer $lexer)
    {
        $this->curToken = Token::illegal('');
        $this->peekToken = Token::illegal('');

        $this->nextToken();
        $this->nextToken();

        // Prefix operators
        $this->registerPrefix(TokenType::VAR, fn () => $this->parseVariableExpression());
        $this->registerPrefix(TokenType::INT, fn () => $this->parseIntegerLiteral());
        $this->registerPrefix(TokenType::BANG, fn () => $this->parsePrefixExpression());
        $this->registerPrefix(TokenType::NULL, fn () => $this->parseNullLiteral());
        $this->registerPrefix(TokenType::FLOAT, fn () => $this->parseFloatLiteral());
        $this->registerPrefix(TokenType::STR, fn () => $this->parseStringLiteral());
        $this->registerPrefix(TokenType::TRUE, fn () => $this->parseBooleanLiteral());
        $this->registerPrefix(TokenType::FALSE, fn () => $this->parseBooleanLiteral());
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
     * @throws CoreParserException
     */
    public function parseProgram(): Program
    {
        /** @var Statement[] $statements */
        $statements = [];

        while (!$this->curTokenIs(TokenType::EOF)) {
            $stmt = $this->parseStatement();

            if ($stmt !== null) {
                $statements[] =  $stmt;
            }

            $this->nextToken();
        }

        return new Program($statements);
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

    /**
     * @throws CoreParserException
     */
    private function expectPeek(TokenType $token): void
    {
        if ($this->peekTokenIs($token)) {
            $this->nextToken();
            return;
        }

        throw new CoreParserException(
            sprintf(
                "expected next token to be %s, got %s instead",
                $token->value,
                $this->peekToken->type->value,
            )
        );
    }

    /**
     * @throws CoreParserException
     */
    private function parseStatement(): Statement|null
    {
        return match ($this->curToken->type) {
            TokenType::HTML => $this->parseHtmlStatement(),
            TokenType::LBRACES => $this->parseEmbeddedCode(),
            default => null,
        };
    }

    /**
     * @throws CoreParserException
     */
    private function parseEmbeddedCode(): Statement
    {
        $this->nextToken();

        return match ($this->curToken->type) {
            TokenType::IF => $this->parseIfStatement(),
            TokenType::LOOP => $this->parseLoopStatement(),
            default => $this->parseExpressionStatement(),
        };
    }

    private function parseHtmlStatement(): HtmlStatement
    {
        return new HtmlStatement($this->curToken);
    }

    /**
     * @throws CoreParserException
     */
    private function parseExpressionStatement(): ExpressionStatement
    {
        $expr = $this->parseExpression(Precedence::LOWEST);
        $result = new ExpressionStatement($this->curToken, $expr);

        if ($this->peekTokenIs(TokenType::RBRACES)) {
            $this->nextToken();
        }

        return $result;
    }

    /**
     * @throws CoreParserException
     */
    private function parseExpression(Precedence $precedence): Expression
    {
        $prefix = $this->prefixParseFns[$this->curToken->type->value] ?? null;

        if ($prefix === null) {
            throw new CoreParserException(
                sprintf('no prefix parse function for character "%s" found', $this->curToken->literal)
            );
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

    private function parseVariableExpression(): Expression
    {
        return new VariableExpression(
            $this->curToken,
            $this->curToken->literal,
        );
    }

    private function parseIntegerLiteral(): Expression
    {
        return new IntegerLiteral(
            $this->curToken,
            (int) $this->curToken->literal,
        );
    }

    private function parseNullLiteral(): Expression
    {
        return new NullLiteral($this->curToken);
    }

    private function parseFloatLiteral(): Expression
    {
        return new FloatLiteral(
            $this->curToken,
            (float) $this->curToken->literal,
        );
    }

    private function parseStringLiteral(): Expression
    {
        return new StringLiteral(
            $this->curToken,
            $this->curToken->literal,
        );
    }

    /**
     * @throws CoreParserException
     */
    private function parsePrefixExpression(): Expression
    {
        $token = $this->curToken;
        $operator = $this->curToken->literal;

        $this->nextToken(); // skip prefix operator

        $right = $this->parseExpression(Precedence::PREFIX);

        return new PrefixExpression($token, $operator, $right);
    }

    private function parseBooleanLiteral(): Expression
    {
        return new BooleanLiteral(
            token: $this->curToken,
            value: $this->curToken->literal === 'true',
        );
    }

    /**
     * @throws CoreParserException
     */
    private function parseIfStatement(): Statement
    {
        $this->nextToken(); // skip "if"

        $condition = $this->parseExpression(Precedence::LOWEST);

        $this->expectPeek(TokenType::RBRACES);

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
     * @throws CoreParserException
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
     * @throws CoreParserException
     */
    private function parseTernaryExpression(Expression $left): Expression
    {
        $this->nextToken(); // skip "?"

        $consequence = $this->parseExpression(Precedence::TERNARY);

        $this->expectPeek(TokenType::COLON);

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
     * @throws CoreParserException
     */
    private function parseLoopStatement(): Statement
    {
        $token = $this->curToken;

        $this->nextToken(); // skip "loop" keyword

        $from = $this->parseExpression(Precedence::LOWEST);

        $this->expectPeek(TokenType::COMMA);

        $this->nextToken();

        $to = $this->parseExpression(Precedence::LOWEST);

        $this->expectPeek(TokenType::RBRACES);

        $this->nextToken();

        $body = $this->parseBlockStatement();

        return new LoopStatement($token, $from, $to, $body);
    }

    /**
     * @throws CoreParserException
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

            if ($stmt !== null) {
                $statements[] = $stmt;
            }

            $this->nextToken();
        }

        return new BlockStatement($token, $statements);
    }
}
