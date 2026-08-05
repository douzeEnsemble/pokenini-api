<?php

declare(strict_types=1);

namespace App\Service;

class VersionService
{
    private const string FALLBACK_VERSION = 'unknown';

    public function __construct(private readonly string $metadataDir) {}

    public function getVersion(): string
    {
        $path = $this->metadataDir.'/version';

        if (!is_readable($path)) {
            return self::FALLBACK_VERSION;
        }

        return trim((string) file_get_contents($path));
    }
}
