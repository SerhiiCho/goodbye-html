<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class BlockObj implements Obj
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

    public function inspect(): string
    {
        $result = '';

        foreach ($this->elements as $element) {
            $result .= $element->inspect();
        }

        return $result;
    }
}
