<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class FormTypesResponse
{
    /**
     * @param FormResponse[] $category
     * @param FormResponse[] $regional
     * @param FormResponse[] $special
     * @param FormResponse[] $variant
     */
    public function __construct(
        public readonly array $category,
        public readonly array $regional,
        public readonly array $special,
        public readonly array $variant,
    ) {}
}
