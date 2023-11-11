<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class IntegerObj extends Obj
{
    public function __construct(public int $value)
    {
    }

    public function type(): ObjType
    {
        return ObjType::INTEGER_OBJ;
    }

    public function value(): int
    {
        return $this->value;
    }
}
