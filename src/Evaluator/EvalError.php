<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Ast\Expressions\VariableExpression;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;

readonly class EvalError
{
    public static function wrongArgumentType(string $type, ObjType $expect, Obj $actual): ErrorObj
    {
        return new ErrorObj(sprintf(
            '[EVAL_ERROR] "%s" is not allowed argument type for "%s", expected "%s"',
            $actual->type()->value,
            $type,
            $expect->value,
        ));
    }

    public static function unknownType(Node $node): ErrorObj
    {
        return new ErrorObj('[EVAL_ERROR] unknown type: "' . get_class($node) . '"');
    }

    public static function operatorNotAllowed(string $operator, Obj $right): ErrorObj
    {
        return new ErrorObj(sprintf('[EVAL_ERROR] operator "%s" is not allowed for type "%s"', $operator, $right->type()->value));
    }

    public static function variableIsUndefined(VariableExpression $node): ErrorObj
    {
        return new ErrorObj(sprintf('[EVAL_ERROR] variable "$%s" is undefined', $node->value));
    }

    public static function infixExpressionMustBeBetweenNumbers(string $side, string $operator, Obj $obj): ErrorObj
    {
        return new ErrorObj(
            sprintf(
                '[EVAL_ERROR] %s side must be INT or FLOAT for operator %s. Got %s',
                $side,
                $operator,
                $obj->type()->value,
            )
        );
    }
}
