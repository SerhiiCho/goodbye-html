<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class Str implements Obj
{
    public function __construct(public string $value)
    {
    }

    public function type(): ObjType
    {
        return ObjType::STRING_OBJ;
    }

    public function inspect(): string
    {
        return $this->value;
    }
}
