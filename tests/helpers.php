<?php declare(strict_types=1);

function exec_private_method(string $class, string $method, object $class_inst, ...$method_args)
{
    $class = new \ReflectionClass($class);
    $new_method = $class->getMethod($method);
    $new_method->setAccessible(true);

    return $new_method->invokeArgs($class_inst, $method_args);
}

function get_path(string $file_name): string {
    return __DIR__ . "/files/{$file_name}.html";
}