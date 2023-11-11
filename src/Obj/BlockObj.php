<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class BlockObj extends Obj
{
    /**
     * @param Obj[] $elements
     */
    public function __construct(public array $elements)
    {
    }

    public function type(): ObjType
    {
        return ObjType::BLOCK_OBJ;
    }

    public function value(): string
    {
        $result = '';

        foreach ($this->elements as $element) {
            $result .= $element->value();
        }

        return $result;
    }
}
