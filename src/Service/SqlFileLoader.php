<?php

declare(strict_types=1);

namespace App\Service;

class SqlFileLoader
{
    public function __construct(private readonly string $sqlDir) {}

    public function load(string $filename): string
    {
        $path = $this->sqlDir.'/'.$filename;

        if (!is_file($path)) {
            throw new \RuntimeException("Failed to read SQL file \"{$filename}\"");
        }

        return (string) file_get_contents($path);
    }
}
