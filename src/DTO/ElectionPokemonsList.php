<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionPokemonsList
{
    public function __construct(
        #[SerializedName('type')]
        private readonly string $listType,
        /** @var int[][]|null[][]|string[][] */
        private readonly array $items,
    ) {}

    public function getListType(): string
    {
        return $this->listType;
    }

    /**
     * @return int[][]|null[][]|string[][]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
