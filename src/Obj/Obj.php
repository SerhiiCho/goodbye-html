<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

interface Obj
{
    public function type(): ObjType;

    public function inspect(): string;
}
