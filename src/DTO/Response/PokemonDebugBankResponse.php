<?php

declare(strict_types=1);

namespace App\DTO\Response;

final class PokemonDebugBankResponse
{
    public function __construct(
        public readonly bool $bankable,
        public readonly ?bool $bankableish,
    ) {}
}
