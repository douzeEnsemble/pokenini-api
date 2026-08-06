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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        $path = $this->metadataDir.'/version';

        if (!is_readable($path)) {
            return null;
        }

        $mtime = filemtime($path);

        if (false === $mtime) {
            return null;
        }

        return (new \DateTimeImmutable())->setTimestamp($mtime);
    }
}
