<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Ast\ExpressionStatement;
use Serhii\GoodbyeHtml\Ast\IntegerLiteral;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Obj\Err;
use Serhii\GoodbyeHtml\Obj\Integer;

readonly class Evaluator
{
    public function eval(Node $node, Env $env): Obj|null
    {
        if ($node instanceof Program) {
            return $this->evalProgram($node, $env);
        } elseif ($node instanceof ExpressionStatement) {
            return $this->eval($node->expression, $env);
        } elseif ($node instanceof IntegerLiteral) {
            return new Integer($node->value);
        }

        return null;
    }

    private function evalProgram(Program $program, Env $env): Obj
    {
        $result = null;

        foreach ($program->statements as $stmt) {
            $result = $this->eval($stmt, $env);

            if ($result instanceof Err) {
                return $result;
            }
        }

        return $result;
    }
}
