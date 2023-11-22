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
    private const PREFIX = '[EVAL_ERROR]';

    public static function wrongArgumentType(string $type, ObjType $expect, Obj $actual): ErrorObj
    {
        return new ErrorObj(sprintf(
            '%s "%s" is not allowed argument type for "%s", expected "%s"',
            self::PREFIX,
            $actual->type()->value,
            $type,
            $expect->value,
        ));
    }

    public static function unknownType(Node $node): ErrorObj
    {
        return new ErrorObj(sprintf('%s unknown type: "%s"', self::PREFIX, get_class($node)));
    }

    public static function operatorNotAllowed(string $operator, Obj $right): ErrorObj
    {
        return new ErrorObj(
            sprintf(
                '%s operator "%s" is not allowed for type "%s"',
                self::PREFIX,
                $operator,
                $right->type()->value,
            )
        );
    }

    public static function variableIsUndefined(VariableExpression $node): ErrorObj
    {
        return new ErrorObj(sprintf('%s variable "$%s" is undefined', self::PREFIX, $node->value));
    }

    public static function infixExpressionMustBeBetweenNumbers(string $side, string $operator, Obj $obj): ErrorObj
    {
        return new ErrorObj(
            sprintf(
                '%s %s side must be INT or FLOAT for operator %s. Got %s',
                self::PREFIX,
                $side,
                $operator,
                $obj->type()->value,
            )
        );
    }
}
