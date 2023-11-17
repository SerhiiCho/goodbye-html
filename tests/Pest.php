<?php

declare(strict_types=1);

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
