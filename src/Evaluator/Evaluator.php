<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Ast\Expressions\InfixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\TernaryExpression;
use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\Ast\Literals\BooleanLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\FloatLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\NullLiteral;
use Serhii\GoodbyeHtml\Ast\Literals\StringLiteral;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Ast\Statements\AssignStatement;
use Serhii\GoodbyeHtml\Ast\Statements\BlockStatement;
use Serhii\GoodbyeHtml\Ast\Statements\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\Statements\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\Statements\IfStatement;
use Serhii\GoodbyeHtml\Ast\Statements\LoopStatement;
use Serhii\GoodbyeHtml\Ast\Statements\Program;
use Serhii\GoodbyeHtml\Obj\BlockObj;
use Serhii\GoodbyeHtml\Obj\BooleanObj;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\FloatObj;
use Serhii\GoodbyeHtml\Obj\HtmlObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\NullObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;

readonly class Evaluator
{
    public function eval(Node $node, Env $env): Obj
    {
        return match (get_class($node)) {
            IntegerLiteral::class => new IntegerObj($node->value),
            FloatLiteral::class => new FloatObj($node->value),
            StringLiteral::class => new StringObj($node->value),
            BooleanLiteral::class => new BooleanObj($node->value),
            NullLiteral::class => new NullObj(),
            HtmlStatement::class => new HtmlObj($node->string()),
            IfStatement::class => $this->evalIfStatement($node, $env),
            BlockStatement::class => $this->evalBlockStatement($node, $env),
            LoopStatement::class => $this->evalLoopStatement($node, $env),
            ExpressionStatement::class => $this->eval($node->expression, $env),
            AssignStatement::class => $this->evalAssignStatement($node, $env),
            PrefixExpression::class => $this->evalPrefixExpression($node, $env),
            InfixExpression::class => $this->evalInfixExpression($node, $env),
            VariableExpression::class => $this->evalVariableExpression($node, $env),
            TernaryExpression::class => $this->evalTernaryExpression($node, $env),
            Program::class => $this->evalProgram($node, $env),
            ErrorObj::class => $node,
            default => EvalError::unknownType($node),
        };
    }

    private function evalProgram(Program $program, Env $env): Obj
    {
        $html = '';

        foreach ($program->statements as $stmt) {
            $stmtObj = $this->eval($stmt, $env);

            if ($stmtObj instanceof ErrorObj) {
                return $stmtObj;
            }

            $html .= $stmtObj->value();
        }

        return new HtmlObj($html);
    }

    private function evalPrefixExpression(PrefixExpression $node, Env $env): Obj
    {
        $right = $this->eval($node->right, $env);

        if ($right instanceof ErrorObj) {
            return $right;
        }

        return match ($node->operator) {
            '-' => $this->evalMinusPrefixOperatorExpression($right),
            '!' => new BooleanObj(!$right->value()),
            default => EvalError::operatorNotAllowed($node->operator, $right),
        };
    }

    private function evalInfixExpression(InfixExpression $node, Env $env): Obj
    {
        $right = $this->eval($node->right, $env);

        if ($right instanceof ErrorObj) {
            return $right;
        }

        $left = $this->eval($node->left, $env);

        if ($left instanceof ErrorObj) {
            return $left;
        }

        return $this->calculateBinaryExpression($left, $right, $node->operator);
    }

    private function calculateBinaryExpression(Obj $left, Obj $right, string $operator): Obj
    {
        $leftValue = $left->value();
        $rightValue = $right->value();

        if ($operator === '.' && ($left instanceof StringObj || $right instanceof StringObj)) {
            return new StringObj($left->value() . $right->value());
        }

        if (!is_numeric($leftValue)) {
            return EvalError::infixExpressionMustBeBetweenNumbers('left', $operator, $left);
        }

        if (!is_numeric($rightValue)) {
            return EvalError::infixExpressionMustBeBetweenNumbers('right', $operator, $right);
        }

        return match ($operator) {
            '+' => $this->numberObject($leftValue + $rightValue),
            '-' => $this->numberObject($leftValue - $rightValue),
            '*' => $this->numberObject($leftValue * $rightValue),
            '/' => $this->numberObject($leftValue / $rightValue),
            '%' => $this->numberObject($leftValue % $rightValue),
            default => EvalError::operatorNotAllowed($operator, $right),
        };
    }

    private function numberObject(int|float $num): Obj
    {
        return is_int($num) ? new IntegerObj($num) : new FloatObj($num);
    }

    private function evalVariableExpression(VariableExpression $node, Env $env): Obj
    {
        $val = $env->get($node->value);

        return $val ?? EvalError::variableIsUndefined($node);
    }

    private function evalIfStatement(IfStatement $node, Env $env): Obj
    {
        $condition = $this->eval($node->condition, $env);

        if ($condition instanceof ErrorObj) {
            return $condition;
        }

        $isTrue = $condition->value();

        if ($isTrue) {
            return $this->eval($node->block, $env);
        }

        // Evaluate else if statements
        foreach ($node->elseIfBlocks as $elseIf) {
            $condition = $this->eval($elseIf->condition, $env);

            if ($condition instanceof ErrorObj) {
                return $condition;
            }

            $isTrue = $condition->value();

            if ($isTrue) {
                return $this->eval($elseIf->block, $env);
            }
        }

        if ($node->elseBlock !== null) {
            return $this->eval($node->elseBlock, $env);
        }

        return new NullObj();
    }

    private function evalTernaryExpression(TernaryExpression $node, Env $env): Obj
    {
        $condition = $this->eval($node->condition, $env);

        if ($condition instanceof ErrorObj) {
            return $condition;
        }

        if ($condition->value()) {
            return $this->eval($node->trueExpression, $env);
        }

        return $this->eval($node->falseExpression, $env);
    }

    private function evalBlockStatement(BlockStatement $node, Env $env): Obj
    {
        /** @var Obj[] $elements */
        $elements = [];

        foreach ($node->statements as $stmt) {
            $stmtObj = $this->eval($stmt, $env);

            if ($stmtObj instanceof ErrorObj) {
                return $stmtObj;
            }

            $elements[] = $stmtObj;
        }

        return new BlockObj($elements);
    }

    private function evalLoopStatement(LoopStatement $node, Env $env): Obj
    {
        $html = '';

        $from = $this->eval($node->from, $env);

        if (!$from instanceof IntegerObj) {
            return EvalError::wrongArgumentType('loop', ObjType::INTEGER_OBJ, $from);
        }

        $to = $this->eval($node->to, $env);

        if (!$to instanceof IntegerObj) {
            return EvalError::wrongArgumentType('loop', ObjType::INTEGER_OBJ, $to);
        }

        for ($i = $from->value; $i <= $to->value; $i++) {
            $env->set('index', new IntegerObj($i));

            $block = $this->eval($node->body, $env);

            if ($block instanceof ErrorObj) {
                return $block;
            }

            $html .= $block->value();
        }

        return new HtmlObj($html);
    }

    private function evalAssignStatement(AssignStatement $node, Env $env): Obj
    {
        $value = $this->eval($node->value, $env);

        if ($value instanceof ErrorObj) {
            return $value;
        }

        $env->set($node->variable->value, $value);

        return new NullObj();
    }

    private function evalMinusPrefixOperatorExpression(Obj $right): Obj
    {
        $value = $right->value();

        return match (gettype($value)) {
            'integer' => new IntegerObj(-$value),
            'double' => new FloatObj(-$value),
            default => EvalError::operatorNotAllowed('-', $right),
        };
    }
}
