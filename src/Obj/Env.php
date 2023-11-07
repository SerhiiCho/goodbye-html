<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class Env
{
    /**
     * @param string[] $store
     */
    public function __construct(
        private array $store = [],
        private ?self $outer = null,
    ) {
    }

    public static function newEnclosedEnv(Env $outer): self
    {
        return new self([], $outer);
    }
}
