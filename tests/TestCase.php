<?php

declare(strict_types=1);

namespace Serhii\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function getPath(string $file_name): string
    {
        return __DIR__ . "/files/{$file_name}.html";
    }
}