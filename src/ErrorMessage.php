<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Obj\ObjType;

readonly class ErrorMessage
{
    public static function typeMismatch(string $type, ObjType $expect, Obj $actual): ErrorObj
    {
        return new ErrorObj("[{$type}] type mismatch: expected {$expect->value} as the first loop argument, got {$actual->type()->value}");
    }
}
