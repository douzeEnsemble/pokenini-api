<?php

declare(strict_types=1);

namespace App\DTO\Response;

/**
 * The forms of every kind, with their name in the language the client asked for.
 */
final class LocalizedFormTypesResponse
{
    /**
     * @param LocalizedFormResponse[] $category
     * @param LocalizedFormResponse[] $regional
     * @param LocalizedFormResponse[] $special
     * @param LocalizedFormResponse[] $variant
     */
    public function __construct(
        public readonly array $category,
        public readonly array $regional,
        public readonly array $special,
        public readonly array $variant,
    ) {}
}
