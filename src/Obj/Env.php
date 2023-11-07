<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

readonly class Env
{
    /**
     * @param Obj[] $store
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

    public function get(string $key): Obj|null
    {
        if (array_key_exists($key, $this->store)) {
            return $this->store[$key];
        }

        if ($this->outer !== null) {
            return $this->outer->get($key);
        }

        return null;
    }

    public function set(string $key, Obj $value): void
    {
        $this->store[$key] = $value;
    }
}
