<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class FloatObj extends Obj
{
    public function __construct(public float $value)
    {
    }

    public function type(): ObjType
    {
        return ObjType::FLOAT_OBJ;
    }

    public function value(): float
    {
        return $this->value;
    }
}
