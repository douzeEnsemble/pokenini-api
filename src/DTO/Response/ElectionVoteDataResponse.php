<?php

declare(strict_types=1);

namespace App\DTO\Response;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ElectionVoteDataResponse
{
    /**
     * @param string[] $winnersSlugs
     * @param string[] $losersSlugs
     */
    public function __construct(
        #[SerializedName('trainer_external_id')]
        public readonly string $trainerExternalId,
        #[SerializedName('dex_slug')]
        public readonly string $dexSlug,
        #[SerializedName('election_slug')]
        public readonly string $electionSlug,
        #[SerializedName('winners_slugs')]
        public readonly array $winnersSlugs,
        #[SerializedName('losers_slugs')]
        public readonly array $losersSlugs,
    ) {}
}
