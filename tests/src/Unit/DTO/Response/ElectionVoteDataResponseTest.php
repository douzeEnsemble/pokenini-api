<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO\Response;

use App\DTO\Response\DexSlugResponse;
use App\DTO\Response\ElectionVoteDataResponse;
use App\DTO\Response\PokemonSlugResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ElectionVoteDataResponse::class)]
final class ElectionVoteDataResponseTest extends TestCase
{
    #[Test]
    public function constructorInitializesProperties(): void
    {
        $dex = new DexSlugResponse(slug: 'national');
        $winner1 = new PokemonSlugResponse(slug: 'pikachu');
        $winner2 = new PokemonSlugResponse(slug: 'raichu');
        $loser = new PokemonSlugResponse(slug: 'magikarp');

        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'abc123',
            dex: $dex,
            electionSlug: 'gen1',
            winners: [$winner1, $winner2],
            losers: [$loser],
        );

        self::assertSame('abc123', $response->trainerExternalId);
        self::assertSame($dex, $response->dex);
        self::assertSame('national', $response->dex->slug);
        self::assertSame('gen1', $response->electionSlug);
        self::assertSame([$winner1, $winner2], $response->winners);
        self::assertSame('pikachu', $response->winners[0]->slug);
        self::assertSame('raichu', $response->winners[1]->slug);
        self::assertSame([$loser], $response->losers);
        self::assertSame('magikarp', $response->losers[0]->slug);
    }

    #[Test]
    public function constructorAcceptsEmptyPokemonArrays(): void
    {
        $dex = new DexSlugResponse(slug: '');
        $response = new ElectionVoteDataResponse(
            trainerExternalId: 'xyz',
            dex: $dex,
            electionSlug: '',
            winners: [],
            losers: [],
        );

        self::assertSame($dex, $response->dex);
        self::assertSame('', $response->dex->slug);
        self::assertSame([], $response->winners);
        self::assertSame([], $response->losers);
    }
}
