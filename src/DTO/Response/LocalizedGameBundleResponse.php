<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * A game bundle with its name in the language the client asked for.
 */
final class LocalizedGameBundleResponse
{
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly GenerationResponse $generation,
    ) {}
}
