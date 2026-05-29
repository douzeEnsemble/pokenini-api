<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class FormsResponse
{
    public function __construct(
        public readonly ?FormResponse $category,
        public readonly ?FormResponse $regional,
        public readonly ?FormResponse $special,
        public readonly ?FormResponse $variant,
    ) {}
}
