<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionPokemonsList
{
    public function __construct(
        #[SerializedName('type')]
        private readonly string $listType,
        /** @var array<array<string, mixed>> */
        private readonly array $items,
    ) {}

    public function getListType(): string
    {
        return $this->listType;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
