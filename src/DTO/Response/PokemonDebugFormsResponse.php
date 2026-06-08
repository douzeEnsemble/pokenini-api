<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugFormsResponse
{
    public function __construct(
        public readonly ?FormDebugResponse $category,
        public readonly ?FormDebugResponse $regional,
        public readonly ?FormDebugResponse $special,
        public readonly ?FormDebugResponse $variant,
    ) {}
}
