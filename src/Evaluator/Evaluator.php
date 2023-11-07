<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Ast\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\StringLiteral;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\HtmlObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;

readonly class Evaluator
{
    public function eval(Node $node, Env $env): Obj|null
    {
        if ($node instanceof Program) {
            return $this->evalProgram($node, $env);
        } elseif ($node instanceof ExpressionStatement) {
            return $this->eval($node->expression, $env);
        } elseif ($node instanceof IntegerLiteral) {
            return new IntegerObj($node->value);
        } elseif ($node instanceof StringLiteral) {
            return new StringObj($node->value);
        } elseif ($node instanceof PrefixExpression) {
            $right = $this->eval($node->right, $env);

            if ($right instanceof ErrorObj) {
                return $right;
            }

            return $this->evalPrefixExpression($node->operator, $right);
        } elseif ($node instanceof HtmlStatement) {
            return new HtmlObj($node->string());
        } elseif ($node instanceof VariableExpression) {
            return $this->evalVariableExpression($node, $env);
        }

        return null;
    }

    private function evalProgram(Program $program, Env $env): Obj|null
    {
        $result = '';

        foreach ($program->statements as $stmt) {
            $obj = $this->eval($stmt, $env);

            if ($result instanceof ErrorObj) {
                return $result;
            }

            if ($obj !== null) {
                $result .= $obj->inspect();
            }
        }

        return new HtmlObj($result);
    }

    private function evalPrefixExpression(string $operator, Obj $right): Obj
    {
        if ($operator === '-') {
            return $this->evalMinusPrefixOperatorExpression($right);
        }

        return new ErrorObj(sprintf('Unknown operator: %s%s', $operator, $right->type()));
    }

    private function evalVariableExpression(VariableExpression $node, Env $env): Obj
    {
        $val = $env->get($node->value);

        if ($val !== null) {
            return $val;
        }

        return new ErrorObj(sprintf('Identifier not found: %s', $node->value));
    }

    private function evalMinusPrefixOperatorExpression(Obj $right): Obj
    {
        if ($right->type() !== ObjType::INTEGER_OBJ) {
            return new ErrorObj(sprintf('Unknown operator: -%s', $right->type()));
        }

        return new IntegerObj(-$right->value);
    }
}
