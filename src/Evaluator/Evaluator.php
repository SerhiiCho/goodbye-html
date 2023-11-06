<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Evaluator;

use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Ast\Node;
use Serhii\GoodbyeHtml\Obj\Integer;

readonly class Evaluator
{
    public function eval(Node $node, Env $env): Obj
    {
        return new Integer($node->value);
    }
}
