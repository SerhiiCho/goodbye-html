<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

use Serhii\GoodbyeHtml\Exceptions\ParserException;

abstract readonly class Obj
{
    abstract public function type(): ObjType;

    abstract public function value(): int|string|bool|float|null;

    /**
     * @throws ParserException
     */
    public static function fromNative(mixed $value, string $name): self
    {
        switch (gettype($value)) {
            case 'string':
                return new StringObj($value);
            case 'integer':
                return new IntegerObj($value);
            case 'boolean':
                return new BooleanObj($value);
            case 'double': // gettype returns 'double' for float values
                return new FloatObj($value);
            case 'NULL':
                return new NullObj();
            default:
                $type = gettype($value);
                $msg = sprintf('[PARSER_ERROR] Provided variable "%s" has unsupported type "%s"', $name, $type);
                throw new ParserException($msg);
        }
    }
}
