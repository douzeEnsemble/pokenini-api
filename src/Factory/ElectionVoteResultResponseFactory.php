<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ElectionVote;
use App\DTO\ElectionVoteResult;
use App\DTO\PokemonElo;
use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\ElectionVoteResultResponse;
use App\DTO\Response\PokemonEloResponse;
use App\DTO\Response\PokemonsEloResponse;
use App\DTO\Response\PokemonSlugResponse;

final class ElectionVoteResultResponseFactory
{
    public static function fromElectionVoteResult(ElectionVoteResult $result): ElectionVoteResultResponse
    {
        return new ElectionVoteResultResponse(
            electionVote: self::buildElectionVoteData($result->getElectionVote()),
            pokemonsElo: self::buildPokemonsElo($result->getPokemonsElo()),
        );
    }

    private static function buildElectionVoteData(ElectionVote $vote): ElectionVoteDataResponse
    {
        return new ElectionVoteDataResponse(
            trainerExternalId: $vote->trainerExternalId,
            dex: new DexSlugResponse(slug: $vote->dexSlug),
            electionSlug: $vote->electionSlug,
            winners: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->winnersSlugs,
            ),
            losers: array_map(
                static fn (string $slug): PokemonSlugResponse => new PokemonSlugResponse(slug: $slug),
                $vote->losersSlugs,
            ),
        );
    }

    /**
     * @param PokemonElo[][] $pokemonsElo
     */
    private static function buildPokemonsElo(array $pokemonsElo): PokemonsEloResponse
    {
        return new PokemonsEloResponse(
            winners: self::buildPokemonEloList($pokemonsElo['winners'] ?? []),
            losers: self::buildPokemonEloList($pokemonsElo['losers'] ?? []),
        );
    }

    /**
     * @param PokemonElo[] $pokemonElos
     *
     * @return PokemonEloResponse[]
     */
    private static function buildPokemonEloList(array $pokemonElos): array
    {
        return array_map(
            static fn (PokemonElo $pokemonElo): PokemonEloResponse => new PokemonEloResponse(
                pokemon: new PokemonSlugResponse(slug: $pokemonElo->getPokemonSlug()),
                elo: $pokemonElo->getElo(),
            ),
            $pokemonElos,
        );
    }
}
