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

        // The file is readable: if it vanishes before this line, SplFileInfo throws, which is the right answer to a race
        return (new \DateTimeImmutable())->setTimestamp((new \SplFileInfo($path))->getMTime());
    }
}
