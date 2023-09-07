<?php

declare(strict_types=1);

namespace Serhii\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static function getPath(string $file_name): string
    {
        return __DIR__ . "/files/{$file_name}.html";
    }

    protected function getFileNames(string $blob): array
    {
        return array_map(static function ($name) {
            $sections = explode('/', $name);
            return [str_replace('.html', '', end($sections))];
        }, glob(__DIR__ . $blob));
    }
}
