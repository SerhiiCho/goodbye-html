<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Ast\BlockStatement;
use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\HtmlStatement;
use Serhii\GoodbyeHtml\Ast\IfExpression;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Ast\LoopExpression;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Ast\PrefixExpression;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Ast\StringLiteral;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\ErrorMessage;
use Serhii\GoodbyeHtml\Obj\BlockObj;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\HtmlObj;
use Serhii\GoodbyeHtml\Obj\IntegerObj;
use Serhii\GoodbyeHtml\Obj\ObjType;
use Serhii\GoodbyeHtml\Obj\StringObj;

readonly class Evaluator
{
    public function eval(Node $node, Env $env): Obj|null
    {
        if ($node instanceof IntegerLiteral) {
            return new IntegerObj($node->value);
        } elseif ($node instanceof StringLiteral) {
            return new StringObj($node->value);
        } elseif ($node instanceof HtmlStatement) {
            return new HtmlObj($node->string());
        } elseif ($node instanceof Program) {
            return $this->evalProgram($node, $env);
        } elseif ($node instanceof ExpressionStatement) {
            return $this->eval($node->expression, $env);
        } elseif ($node instanceof PrefixExpression) {
            $right = $this->eval($node->right, $env);

            if ($right instanceof ErrorObj) {
                return $right;
            }

            return $this->evalPrefixExpression($node->operator, $right);
        } elseif ($node instanceof VariableExpression) {
            return $this->evalVariableExpression($node, $env);
        } elseif ($node instanceof IfExpression) {
            return $this->evalIfExpression($node, $env);
        } elseif ($node instanceof BlockStatement) {
            return $this->evalBlockStatement($node, $env);
        } elseif ($node instanceof LoopExpression) {
            return $this->evalLoopExpression($node, $env);
        } elseif ($node instanceof ErrorObj) {
            return $node;
        }

        return new ErrorObj('Unknown node type: ' . get_class($node));
    }

    private function evalProgram(Program $program, Env $env): Obj|null
    {
        $html = '';

        foreach ($program->statements as $stmt) {
            $stmtObj = $this->eval($stmt, $env);

            if ($stmtObj instanceof ErrorObj) {
                return $stmtObj;
            }

            if ($stmtObj !== null) {
                $html .= $stmtObj->inspect();
            }
        }

        return new HtmlObj($html);
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

    private function evalIfExpression(IfExpression $node, Env $env): Obj
    {
        $condition = $this->eval($node->condition, $env);

        if ($condition instanceof ErrorObj) {
            return $condition;
        }

        if ($condition->value) {
            return $this->eval($node->consequence, $env);
        }

        return $this->eval($node->alternative, $env);
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

            if ($stmtObj !== null) {
                $elements[] = $stmtObj;
            }
        }

        return new BlockObj($elements);
    }

    private function evalLoopExpression(LoopExpression $node, Env $env): Obj
    {
        $html = '';

        $from = $this->eval($node->from, $env);

        if (!$from instanceof IntegerObj) {
            return ErrorMessage::typeMismatch('loop', ObjType::INTEGER_OBJ, $from);
        }

        $to = $this->eval($node->to, $env);

        if (!$to instanceof IntegerObj) {
            return ErrorMessage::typeMismatch('loop', ObjType::INTEGER_OBJ, $to);
        }

        for ($i = $from->value; $i <= $to->value; $i++) {
            $env->set('index', new IntegerObj($i));

            $block = $this->eval($node->body, $env);

            if ($block instanceof ErrorObj) {
                return $block;
            }

            if ($block !== null) {
                $html .= $block->inspect();
            }
        }

        return new HtmlObj($html);
    }

    private function evalMinusPrefixOperatorExpression(Obj $right): Obj
    {
        if ($right->type() !== ObjType::INTEGER_OBJ) {
            return new ErrorObj(sprintf('Unknown operator: -%s', $right->type()));
        }

        return new IntegerObj(-$right->value);
    }
}
