<?php declare(strict_types=1);

function get_path(string $file_name): string {
    return __DIR__ . "/files/{$file_name}.html";
}