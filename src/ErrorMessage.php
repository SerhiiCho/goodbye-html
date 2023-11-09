<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Ast\VariableExpression;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;

readonly class ErrorMessage
{
    public static function wrongArgumentType(string $type, ObjType $expect, Obj $actual): ErrorObj
    {
        return new ErrorObj(sprintf(
            '[ERROR] "%s" is not allowed argument type for "%s", expected "%s"',
            $actual->type()->value,
            $type,
            $expect->value,
        ));
    }

    public static function unknownType(Node $node): ErrorObj
    {
        return new ErrorObj('[ERROR] unknown type: "' . get_class($node) . '"');
    }

    public static function unknownOperator(string $operator, Obj $right): ErrorObj
    {
        return new ErrorObj(sprintf('[ERROR] unknown operator "%s%s"', $operator, $right->type()->value));
    }

    public static function variableIsUndefined(VariableExpression $node): ErrorObj
    {
        return new ErrorObj(sprintf('[ERROR] variable "$%s" is undefined', $node->value));
    }
}
