<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class ImageCreditGroupResponse
{
    /**
     * @param ImageCreditImageResponse[] $images
     */
    public function __construct(
        public readonly string $credit,
        public readonly array $images,
    ) {}
}
