<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class NullObj extends Obj
{
    public function __construct()
    {
    }

    public function type(): ObjType
    {
        return ObjType::NULL_OBJ;
    }

    public function value(): null
    {
        return null;
    }
}
