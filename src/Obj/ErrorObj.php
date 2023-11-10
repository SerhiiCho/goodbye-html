<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class ErrorObj extends Obj
{
    public function __construct(public string $message)
    {
    }

    public function type(): ObjType
    {
        return ObjType::ERROR_OBJ;
    }

    public function inspect(): string
    {
        return $this->message;
    }
}
