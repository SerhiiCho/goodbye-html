<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

class Env
{
    /**
     * @param array<string,Obj> $store
     */
    public function __construct(
        private array $store = [],
        private readonly ?self $outer = null,
    ) {
    }

    // todo: it's going to be used in the next version when I implement scopes
    public static function newEnclosedEnv(Env $outer): self
    {
        return new self([], $outer);
    }

    public function get(string $key): Obj|null
    {
        if (array_key_exists($key, $this->store)) {
            return $this->store[$key];
        }

        return $this->outer?->get($key);
    }

    public function set(string $key, Obj $value): void
    {
        $this->store[$key] = $value;
    }

    /**
     * @param array<string,mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        return new self($arr);
    }
}
