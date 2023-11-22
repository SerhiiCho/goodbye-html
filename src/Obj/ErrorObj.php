<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class ErrorObj extends Obj
{
    /**
     * @param non-empty-string $message
     */
    public function __construct(public string $message)
    {
    }

    public function type(): ObjType
    {
        return ObjType::ERROR_OBJ;
    }

    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->message;
    }
}
