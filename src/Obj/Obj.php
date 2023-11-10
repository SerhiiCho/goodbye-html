<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

use Serhii\GoodbyeHtml\Exceptions\ParserException;

abstract readonly class Obj
{
    abstract public function type(): ObjType;

    abstract public function value(): int|string|bool;

    public static function fromNative(mixed $value, string $name): self
    {
        if (is_string($value)) {
            return new StringObj($value);
        } elseif (is_int($value)) {
            return new IntegerObj($value);
        } else {
            $msg = sprintf('Provided variable "%s" has unsupported type "%s"', $name, gettype($value));
            throw new ParserException($msg);
        }
    }
}
