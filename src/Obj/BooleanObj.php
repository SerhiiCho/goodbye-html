<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class BooleanObj extends Obj
{
    public function __construct(public bool $value)
    {
    }

    public function type(): ObjType
    {
        return ObjType::BOOLEAN_OBJ;
    }

    public function value(): bool
    {
        return $this->value;
    }
}
