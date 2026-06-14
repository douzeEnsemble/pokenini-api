<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteDataResponse
{
    /**
     * @param PokemonSlugResponse[] $winners
     * @param PokemonSlugResponse[] $losers
     */
    public function __construct(
        public readonly TrainerExternalIdResponse $trainer,
        public readonly DexSlugResponse $dex,
        #[SerializedName('election_slug')]
        public readonly string $electionSlug,
        public readonly array $winners,
        public readonly array $losers,
    ) {}
}
