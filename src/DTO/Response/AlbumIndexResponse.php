<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class AlbumIndexResponse
{
    /**
     * @param AlbumPokemonResponse[] $pokemons
     */
    public function __construct(
        public readonly ?AlbumDexResponse $dex,
        public readonly array $pokemons,
        public readonly AlbumReportResponse $report,
        #[SerializedName('filtered_report')]
        public readonly AlbumReportResponse $filteredReport,
    ) {}
}
