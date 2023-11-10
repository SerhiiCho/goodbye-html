<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class HtmlObj extends Obj
{
    public function __construct(public string $html)
    {
    }

    public function type(): ObjType
    {
        return ObjType::HTML_OBJ;
    }

    public function inspect(): string
    {
        return $this->html;
    }
}
